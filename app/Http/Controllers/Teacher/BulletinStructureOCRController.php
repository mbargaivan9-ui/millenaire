<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\BulletinStructure;
use App\Models\Classe;
use App\Services\BulletinOCRService;
use App\Services\BulletinStructureParserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * BulletinStructureOCRController
 * 
 * Contrôleur pour:
 * 1. Upload d'image/PDF de bulletin
 * 2. Extraction OCR
 * 3. Parsing de structure
 * 4. Vérification et sauvegarde
 */
class BulletinStructureOCRController extends Controller
{
    protected BulletinOCRService $ocrService;
    protected BulletinStructureParserService $parserService;

    public function __construct(
        BulletinOCRService $ocrService,
        BulletinStructureParserService $parserService
    ) {
        $this->ocrService = $ocrService;
        $this->parserService = $parserService;
    }

    /**
     * Formulaire d'upload
     */
    public function createForm(Classe $classe)
    {
        $this->authorize('update', $classe);

        return view('teacher.bulletin-structure-ocr.create', [
            'classe' => $classe,
        ]);
    }

    /**
     * Traiter l'upload et l'OCR
     */
    public function processUpload(Request $request, Classe $classe)
    {
        $this->authorize('update', $classe);

        $request->validate([
            'bulletin_image' => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240', // 10MB max
        ], [
            'bulletin_image.required' => 'Veuillez sélectionner une image ou PDF',
            'bulletin_image.mimes' => 'Format accepté: JPG, PNG, PDF',
            'bulletin_image.max' => 'Fichier trop volumineux (max 10MB)',
        ]);

        try {
            // 1. Récupérer le fichier
            $file = $request->file('bulletin_image');

            // 2. Traiter via OCR
            $ocrResult = $this->ocrService->processFile($file);

            if (!$ocrResult['success']) {
                return back()->with('error', 'Erreur OCR: ' . ($ocrResult['error'] ?? 'Unknown error'));
            }

            // 3. Parser la structure
            $structure = $this->parserService->parseStructure(
                $ocrResult['text'] ?? '',
                $ocrResult['tables'] ?? []
            );
            $calculationRules = $this->parserService->parseCalculationRules(
                $ocrResult['text'] ?? '',
                $structure
            );

            // 4. Stocker l'image source
            $storagePath = 'bulletins/ocr/' . $classe->id . '/' . uniqid() . '.' . $file->extension();
            Storage::disk('local')->putFileAs('', $file, $storagePath);

            // 5. Vérifier confiance OCR
            $confidence = $ocrResult['confidence'] ?? 75;
            if (!$this->ocrService->isConfidenceAcceptable($confidence)) {
                return back()->with('warning', 
                    "Confiance OCR faible ($confidence%). Veuillez vérifier et corriger la structure manuellement."
                );
            }

            // 6. Stocker temporairement en session pour vérification
            session([
                'ocr_structure' => $structure,
                'ocr_rules' => $calculationRules,
                'ocr_confidence' => $confidence,
                'ocr_image_path' => $storagePath,
                'ocr_method' => $ocrResult['method'] ?? 'unknown',
            ]);

            // 7. Rediriger vers vérification
            return redirect()->route('teacher.bulletin-structure-ocr.verify', $classe)
                ->with('success', "OCR complété avec confiance $confidence%. Vérifiez les détails detected.");

        } catch (\Exception $e) {
            \Log::error('OCR Error', ['error' => $e->getMessage()]);
            return back()->with('error', 'Erreur OCR: ' . $e->getMessage());
        }
    }

    /**
     * Formulaire de vérification et correction
     */
    public function showVerification(Classe $classe)
    {
        $this->authorize('update', $classe);

        $structure = session('ocr_structure');
        $rules = session('ocr_rules');
        $confidence = session('ocr_confidence');

        if (!$structure || !$rules) {
            return redirect()->route('teacher.bulletin-structure-ocr.create', $classe)
                ->with('error', 'Session expirée. Réessayez.');
        }

        return view('teacher.bulletin-structure-ocr.verify', [
            'classe' => $classe,
            'structure' => $structure,
            'rules' => $rules,
            'confidence' => $confidence,
        ]);
    }

    /**
     * Sauvegarder la structure vérifiée/corrigée avec field_coordinates en base données
     * 
     * Crée un enregistrement BulletinStructure complet avec:
     * - Structure JSON (sujets, coefficients, formules)
     * - Règles de calcul (moyennes, rang, appréciations)
     * - Coordonnées des zones OCR pour réutilisation
     * - Métadonnées et audit
     * 
     * Seul le professeur principal autorisé peut faire cela
     */
    public function saveStructure(Request $request, Classe $classe)
    {
        $user = auth()->user();
        $teacher = $user->teacher;
        
        // Vérifier autorisation: prof principal de cette classe
        abort_unless($teacher && $teacher->is_prof_principal, 403,
            'Seul un professeur principal peut sauvegarder les structures'
        );
        
        $isHeadTeacher = $classe->head_teacher_id === $teacher->id ||
                        $teacher->headClasses()->where('classe_id', $classe->id)->exists();
        
        abort_unless($isHeadTeacher, 403, 
            'Vous n\'êtes pas le professeur principal de cette classe'
        );

        // Validation complète des données
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'subjects' => 'required|array|min:1',
            'subjects.*' => 'string|max:100',
            'coefficients' => 'required|array',
            'coefficients.*' => 'numeric|min:0.1|max:10',
            'appreciation_rules' => 'required|array',
            'appreciation_rules.*' => 'string|max:100',
            'mean_formula' => 'required|string|max:500',
            'rounding_mode' => 'required|in:round,floor,ceil',
            'grading_min' => 'required|numeric|min:0',
            'grading_max' => 'required|numeric|gt:grading_min',
        ], [
            'name.required' => 'Le nom de la structure est requis',
            'subjects.required' => 'Au moins une matière est requise',
            'coefficients.required' => 'Les coefficients sont requis',
            'mean_formula.required' => 'La formule de calcul de la moyenne est requise',
        ]);

        try {
            // Récupérer les coordonnées de zones sauvegardées en session
            $fieldCoordinates = session('ocr_field_coordinates', []);
            $ocrText = session('ocr_structure_text', '');
            $ocrMethod = session('ocr_method', 'unknown');
            $ocrConfidence = session('ocr_confidence', 75);
            $imagePath = session('ocr_image_path');

            // Si aucune coordonnée en session, chercher les parser des données POST (fallback)
            if (empty($fieldCoordinates)) {
                $fieldCoordinates = $request->input('field_coordinates', []);
            }

            // Construire la structure complète
            $structure = [
                'subjects' => $validated['subjects'],
                'coefficients' => $validated['coefficients'],
                'grading_scale' => [
                    'min' => $validated['grading_min'],
                    'max' => $validated['grading_max'],
                ],
                'appreciation_rules' => $validated['appreciation_rules'],
                'field_coordinates' => $fieldCoordinates, // Coordonnées OCR pour réutilisation
                'field_labels' => [
                    'matieres' => 'Colonnes Matières',
                    'notes' => 'Colonnes Notes',
                    'coefficients' => 'Colonnes Coefficients',
                    'moyennes' => 'Colonnes Moyennes',
                    'moy_generale' => 'Moyenne Générale',
                    'rang' => 'Rang',
                    'appreciations' => 'Appréciations',
                ],
            ];

            // Construire les règles de calcul
            $rules = [
                'formulas' => [
                    'moyenne' => $validated['mean_formula'],
                    'rang' => session('ocr_rules.formulas.rang', 'rank_by_average'),
                    'appréciation' => session('ocr_rules.formulas.appréciation', 'by_average_threshold'),
                ],
                'rounding' => $validated['rounding_mode'],
                'validation_rules' => [
                    'min_grade' => $validated['grading_min'],
                    'max_grade' => $validated['grading_max'],
                    'allow_decimals' => true,
                ],
                'special_cases' => session('ocr_rules.special_cases', []),
                'ocr_metadata' => [
                    'method' => $ocrMethod,
                    'confidence' => $ocrConfidence,
                    'text_sample' => substr($ocrText, 0, 200),
                    'extracted_at' => session('ocr_extracted_at', now()->toIso8601String()),
                ],
            ];

            // Vérifier l'unicité du nom pour cette classe
            $existing = BulletinStructure::where('classe_id', $classe->id)
                ->where('name', $validated['name'])
                ->where('id', '!=', $request->input('id', 0))
                ->first();

            if ($existing) {
                return back()->with('error', 
                    'Une structure avec ce nom existe déjà pour cette classe.'
                );
            }

            // Créer l'enregistrement BulletinStructure
            $bulletinStructure = BulletinStructure::create([
                'classe_id' => $classe->id,
                'name' => $validated['name'],
                'description' => $validated['description'],
                'source_image_path' => $imagePath,
                'structure_json' => $structure,
                'calculation_rules' => $rules,
                'ocr_confidence' => $ocrConfidence,
                'is_verified' => false, // Admin doit valider
                'is_active' => true,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            // Log l'action pour audit
            \Log::info('OCR: BulletinStructure created and saved to database', [
                'bulletin_structure_id' => $bulletinStructure->id,
                'classe_id' => $classe->id,
                'class_name' => $classe->name,
                'subjects_count' => count($validated['subjects']),
                'field_coordinates_count' => count($fieldCoordinates),
                'ocr_method' => $ocrMethod,
                'ocr_confidence' => $ocrConfidence,
                'created_by' => auth()->id(),
                'teacher_id' => $teacher->id,
            ]);

            // Log d'activité pour audit trail
            if (class_exists('Spatie\Activitylog\Facades\Activity')) {
                activity()
                    ->causedBy(auth()->user())
                    ->performedOn($bulletinStructure)
                    ->withProperties([
                        'classe_id' => $classe->id,
                        'class_name' => $classe->name,
                        'subjects_count' => count($validated['subjects']),
                        'has_field_coordinates' => count($fieldCoordinates) > 0,
                        'ocr_confidence' => $ocrConfidence,
                        'ocr_method' => $ocrMethod,
                    ])
                    ->log('Structure de bulletin créée via OCR');
            }

            // Nettoyer la session
            session()->forget([
                'ocr_structure',
                'ocr_structure_text',
                'ocr_rules',
                'ocr_confidence',
                'ocr_image_path',
                'ocr_method',
                'ocr_field_coordinates',
                'ocr_class_id',
                'ocr_extracted_at',
                'ocr_zones_saved_at',
            ]);

            // Rediriger avec succès
            return redirect()->route('teacher.bulletin-structure-ocr.show', $bulletinStructure)
                ->with('success', 
                    'Structure de bulletin créée avec succès ! ' .
                    'Les administat teurs vont vérifier et valider la structure. ' .
                    'En attendant, vous pouvez continuer à travailler ou créer d\'autres structures.'
                );

        } catch (\Exception $e) {
            \Log::error('Save BulletinStructure Error', [
                'error' => $e->getMessage(),
                'classe_id' => $classe->id,
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return back()->with('error', 
                'Erreur sauvegarde: ' . $e->getMessage()
            );
        }
    }

    /**
     * Afficher structures existantes
     */
    public function index()
    {
        $user = Auth::user();
        $teacher = $user->teacher;

        // Vérifier que le teacher existe
        if (!$teacher) {
            return redirect()->route('teacher.dashboard')
                ->with('error', 'Profil enseignant non trouvé.');
        }

        // Si prof principal, voir toutes les structures de ses classes
        // Sinon, voir les structures de ses classes de matière
        if ($user->isProfPrincipal()) {
            $classes = $teacher->headClasses ?? collect(); // Classes où prof principal
        } else {
            $classes = $teacher->classes()->pluck('classes.id') ?? collect();
        }

        // Sécuriser: s'assurer que $classes n'est pas null et peut être converti en array
        $classIds = ($classes && $classes->count() > 0) ? $classes->toArray() : [0];

        $bulletinStructures = BulletinStructure::whereIn('classe_id', $classIds)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('teacher.bulletin-structure-ocr.index', [
            'bulletinStructures' => $bulletinStructures,
            'classes' => $classes,
        ]);
    }

    /**
     * Afficher détails d'une structure
     */
    public function show(BulletinStructure $structure)
    {
        $this->authorize('view', $structure->classe);

        return view('teacher.bulletin-structure-ocr.show', [
            'structure' => $structure,
            'structure_data' => $structure->getStructure(),
            'rules_data' => $structure->getCalculationRules(),
        ]);
    }

    /**
     * Éditer une structure
     */
    public function edit(BulletinStructure $structure)
    {
        $this->authorize('update', $structure->classe);

        return view('teacher.bulletin-structure-ocr.edit', [
            'structure' => $structure,
            'structure_data' => $structure->getStructure(),
            'rules_data' => $structure->getCalculationRules(),
        ]);
    }

    /**
     * Mettre à jour structure
     */
    public function update(Request $request, BulletinStructure $structure)
    {
        $this->authorize('update', $structure->classe);

        $request->validate([
            'subjects' => 'required|array|min:1',
            'coefficients' => 'required|array',
            'appreciation_rules' => 'required|array',
        ]);

        $structure->update([
            'structure_json' => [
                'subjects' => $request->input('subjects'),
                'coefficients' => $request->input('coefficients'),
                'grading_scale' => $structure->getStructure()['grading_scale'],
                'appreciation_rules' => $request->input('appreciation_rules'),
            ],
            'updated_by' => Auth::id(),
        ]);

        return back()->with('success', 'Structure mise à jour avec succès.');
    }

    /**
     * Supprimer structure
     */
    public function destroy(BulletinStructure $structure)
    {
        $this->authorize('delete', $structure->classe);

        Storage::delete($structure->source_image_path);
        $structure->delete();

        return back()->with('success', 'Structure supprimée.');
    }
}
