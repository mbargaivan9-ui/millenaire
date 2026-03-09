<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Services\BulletinOCRService;
use App\Services\SimpleOCRService;
use App\Services\BulletinStructureParserService;
use App\Services\OCRZoneDetectionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * BulletinOCRAPIController
 * 
 * Endpoint API pour traiter les uploads OCR depuis le wizard
 * 
 * Service OCR: BulletinOCRService avec Tesseract (local) + fallback OCR.Space
 * - ✓ Tesseract: Unlimited requests (local OCR)
 * - ✓ Fallback OCR.Space: 25 requêtes gratuites/jour
 * - ✓ Support multilingue (français + anglais)
 * - ✓ Compatible avec tous les OS
 */
class BulletinOCRAPIController extends Controller
{
    protected BulletinOCRService $ocrService;
    protected SimpleOCRService $simpleOCR;
    protected BulletinStructureParserService $parserService;
    protected OCRZoneDetectionService $zoneDetection;

    public function __construct(
        BulletinOCRService $ocrService,
        SimpleOCRService $simpleOCR,
        BulletinStructureParserService $parserService,
        OCRZoneDetectionService $zoneDetection
    ) {
        $this->ocrService = $ocrService;
        $this->simpleOCR = $simpleOCR;
        $this->parserService = $parserService;
        $this->zoneDetection = $zoneDetection;
    }

    /**
     * Créer le lien symbolique storage/app/public → public/storage
     */
    private function createStorageLink()
    {
        $link = public_path('storage');
        $target = storage_path('app/public');
        
        // Si le lien existe déjà, le supprimer
        if (is_link($link)) {
            unlink($link);
        } elseif (is_dir($link)) {
            rmdir($link);
        }
        
        // Windows: utiliser mklink
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $cmd = "mklink /D \"$link\" \"$target\"";
            exec($cmd, $output, $returnVar);
            if ($returnVar !== 0) {
                throw new \Exception("Impossible de créer le lien symbolique Windows: " . implode(' ', $output));
            }
        } else {
            // Linux/Mac: utiliser symlink
            if (!symlink($target, $link)) {
                throw new \Exception("Impossible de créer le lien symbolique Unix");
            }
        }
    }

    /**
     * Traiter upload de fichier OCR
     * POST /teacher/bulletin/ocr/upload
     * 
     * Processus:
     * 1. Valide le fichier (image ou PDF)
     * 2. Exécute l'extraction OCR (Tesseract ou OCR.Space)
     * 3. Parse la structure (matières, coefficients, formules)
     * 4. Retourne les données pour l'interface de mapping de zones
     * 
     * Autorisation: Professeur principal uniquement
     */
    public function processUpload(Request $request): JsonResponse
    {
        try {
            // Vérifier l'authentification et l'autorisation (prof principal)
            $user = auth()->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Non authentifié. Veuillez vous connecter.',
                ], 401);
            }

            $teacher = $user->teacher;
            if (!$teacher || !$teacher->is_prof_principal) {
                return response()->json([
                    'success' => false,
                    'message' => 'Seul un professeur principal peut utiliser l\'OCR Wizard.',
                ], 403);
            }

            // Valider le fichier
            $request->validate([
                'file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:51200', // 50MB
            ], [
                'file.required' => 'Un fichier est requis',
                'file.mimes' => 'Format accepté: JPG, PNG, PDF',
                'file.max' => 'Fichier trop volumineux (max 50MB)',
            ]);

            $file = $request->file('file');
            
            \Log::info('OCR Upload: Starting OCR extraction', [
                'filename' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime' => $file->getMimeType(),
                'user_id' => auth()->id(),
                'teacher_id' => $teacher->id,
            ]);

            // Extraire via Tesseract (BulletinOCRService)
            $ocrResult = $this->ocrService->processFile($file);

            // Fallback vers SimpleOCR si Tesseract échoue
            if (!($ocrResult['success'] ?? false)) {
                \Log::warning('Tesseract failed, trying SimpleOCR fallback', [
                    'error' => $ocrResult['error'] ?? 'Unknown',
                ]);
                $ocrResult = $this->simpleOCR->extract($file);
            }

            if (!($ocrResult['success'] ?? false)) {
                \Log::error('All OCR methods failed', [
                    'tesseract_error' => $ocrResult['error'] ?? 'Unknown',
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur OCR: ' . ($ocrResult['error'] ?? 'Service indisponible. Vérifiez que Tesseract est installé.'),
                    'backend' => $ocrResult['backend'] ?? 'unknown',
                ], 400);
            }

            // ✅ ÉTAPE 1: SAUVEGARDER L'IMAGE EN PREMIER
            $previewUrl = null;
            $imagePathForPreview = null;
            try {
                // Vérifier le lien symbolique storage
                if (!is_link(public_path('storage')) && !is_dir(public_path('storage'))) {
                    \Log::warning('Lien symbolique storage manquant, creation...');
                    try {
                        $this->createStorageLink();
                    } catch (\Exception $e) {
                        \Log::warning('Ne peut pas créer lien symbolique: ' . $e->getMessage());
                    }
                }
                
                // Créer le répertoire s'il n'existe pas
                if (!Storage::disk('public')->exists('bulletins/ocr')) {
                    Storage::disk('public')->makeDirectory('bulletins/ocr', 0755, true);
                }
                
                $filename = 'bulletins/ocr/' . uniqid() . '_' . time() . '.' . $file->getClientOriginalExtension();
                $fileContent = file_get_contents($file->getRealPath());
                Storage::disk('public')->put($filename, $fileContent);
                
                // Générer l'URL - utilisé url() pour la rendre absolue
                $relativeUrl = Storage::url($filename);
                
                // Vérifier que le fichier existe physiquement
                $fullPath = Storage::disk('public')->path($filename);
                if (!file_exists($fullPath)) {
                    throw new \Exception("Fichier non trouvé après écriture: $fullPath");
                }
                
                // Générer l'URL complète
                $previewUrl = url($relativeUrl);
                $imagePathForPreview = $fullPath;
                
                \Log::info('✅ Image stockée avec succès', [
                    'filename' => $filename,
                    'relative_url' => $relativeUrl,
                    'absolute_url' => $previewUrl,
                    'file_exists' => file_exists($fullPath),
                    'file_size' => filesize($fullPath),
                ]);
            } catch (\Exception $e) {
                \Log::error('❌ Erreur lors du stockage de l\'image', [
                    'error' => $e->getMessage(),
                    'file' => $file->getClientOriginalName(),
                    'trace' => $e->getTraceAsString(),
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de sauvegarder l\'image. ' . $e->getMessage(),
                ], 500);
            }

            $text = $ocrResult['text'] ?? '';
            $isFallback = $ocrResult['is_fallback'] ?? false;
            $confidence = $ocrResult['confidence'] ?? ($isFallback ? 0 : 75);

            if (empty($text)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun texte n\'a pu être extrait du fichier. Vérifiez la qualité de l\'image.',
                    'preview_url' => $previewUrl, // Retourner l'image même en cas d'erreur
                ], 400);
            }

            // ✅ ÉTAPE 2: PARSER LA STRUCTURE
            try {
                $structure = $this->parserService->parseStructure(
                    $text,
                    $ocrResult['tables'] ?? [] // Passer les tables détectées
                );

                $rules = $this->parserService->parseCalculationRules(
                    $text,
                    $structure
                );
            } catch (\Exception $e) {
                \Log::error('Parser error', ['error' => $e->getMessage()]);
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur parsing: ' . $e->getMessage(),
                    'preview_url' => $previewUrl, // Retourner l'image même en cas d'erreur
                ], 400);
            }

            \Log::info('OCR: Extraction and parsing complete', [
                'method' => $ocrResult['method'] ?? 'unknown',
                'confidence' => $confidence,
                'subjects_found' => count($structure['subjects'] ?? []),
                'tables_detected' => count($ocrResult['tables'] ?? []),
                'is_fallback' => $isFallback,
            ]);

            // ✅ ÉTAPE 3: DÉTECTER LES ZONES OCR POUR VISUALISATION
            $ocrZones = [];
            if ($imagePathForPreview && file_exists($imagePathForPreview)) {
                try {
                    $zoneDetectionResult = $this->zoneDetection->detectZones($imagePathForPreview);
                    if ($zoneDetectionResult['success']) {
                        $ocrZones = $zoneDetectionResult['zones'];
                        \Log::info('Zones détectées avec succès', [
                            'zones_count' => count($ocrZones),
                        ]);
                    }
                } catch (\Exception $e) {
                    \Log::warning('Zone detection failed', ['error' => $e->getMessage()]);
                }
            }

            return response()->json([
                'success' => true,
                'raw_text' => $text,
                'text' => substr($text, 0, 500) . (strlen($text) > 500 ? '...' : ''),
                'confidence' => $confidence,
                'preview_url' => $previewUrl,
                'ocr_zones' => $ocrZones,  // Zones détectées avec positions
                'structure' => $structure,
                'rules' => $rules,
                'method' => $ocrResult['method'] ?? 'ocr.space',
                'is_fallback' => $isFallback,
                'tables_detected' => count($ocrResult['tables'] ?? []),
                'message' => $isFallback 
                    ? "Extraction en fallback. Les données peuvent être partielles. Vérifiez le résultat attentivement."
                    : "OCR complété avec confiance $confidence%",
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::warning('OCR Upload validation error', ['errors' => $e->errors()]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation: ' . collect($e->errors())->flatten()->first(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('OCR API error', [
                'error' => $e->getMessage(),
                'class' => get_class($e),
                'user_id' => auth()->id() ?? null,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur serveur: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Sauvegarder les coordonnées des zones avec validation complète
     * POST /teacher/bulletin/ocr/save-structure
     * 
     * Enregistre les coordonnées des zones (field_coordinates) pour réutilisation
     * sur les futurs imports de bulletins de la même classe
     * 
     * Format attendu:
     * {
     *   "field_coordinates": {
     *     "matieres": {"x": 10, "y": 20, "width": 100, "height": 200, ...},
     *     "notes": {...},
     *     ...
     *   },
     *   "class_id": 5
     * }
     */
    public function upload(Request $request): JsonResponse
    {
        return $this->processUpload($request);
    }

    /**
     * Get zones for existing bulletin
     * GET /api/v1/teacher/bulletin/ocr/{id}/ocr-zones
     */
    public function getZones($bulletinId): JsonResponse
    {
        try {
            $bulletin = \App\Models\Bulletin::findOrFail($bulletinId);
            
            // Vérifier l'autorisation
            $user = auth()->user();
            $teacher = $user->teacher;
            
            $canAccess = $bulletin->classe->head_teacher_id === $teacher->id || 
                        $teacher->headClasses()->where('classe_id', $bulletin->class_id)->exists();
            
            abort_unless($canAccess, 403, 'Non autorisé à accéder à ce bulletin');
            
            return response()->json([
                'success' => true,
                'zones' => $bulletin->ocr_zones ?? [],
                'image_url' => $bulletin->image_path ? asset('storage/' . $bulletin->image_path) : null,
                'raw_text' => $bulletin->raw_text ?? '',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Bulletin non trouvé',
            ], 404);
        } catch (\Exception $e) {
            \Log::error('OCR getZones error', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des zones',
            ], 500);
        }
    }

    /**
     * Save corrected zones
     * POST /api/v1/teacher/bulletin/ocr/{id}/ocr-zones
     */
    public function saveZones($bulletinId, Request $request): JsonResponse
    {
        try {
            $bulletin = \App\Models\Bulletin::findOrFail($bulletinId);
            
            // Vérifier l'autorisation
            $user = auth()->user();
            $teacher = $user->teacher;
            
            $canAccess = $bulletin->classe->head_teacher_id === $teacher->id || 
                        $teacher->headClasses()->where('classe_id', $bulletin->class_id)->exists();
            
            abort_unless($canAccess, 403, 'Non autorisé à modifier ce bulletin');
            
            // Valider les zones
            $validated = $request->validate([
                'zones' => 'required|array',
                'zones.*' => 'array',
            ]);
            
            // Sauvegarder
            $bulletin->ocr_zones = $validated['zones'];
            $bulletin->processed_at = now();
            $bulletin->save();
            
            \Log::info('OCR: Zones saved', [
                'bulletin_id' => $bulletinId,
                'user_id' => auth()->id(),
                'zones_count' => count($validated['zones']),
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Zones sauvegardées avec succès',
                'bulletin_id' => $bulletinId,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('OCR saveZones error', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la sauvegarde des zones',
            ], 500);
        }
    }

    /**
     * Sauvegarder les coordonnées des zones avec validation complète
     * POST /teacher/bulletin/ocr/save-structure
     * 
     * Enregistre les coordonnées des zones (field_coordinates) pour réutilisation
     * sur les futurs imports de bulletins de la même classe
     * 
     * Format attendu:
     * {
     *   "field_coordinates": {
     *     "matieres": {"x": 10, "y": 20, "width": 100, "height": 200, ...},
     *     "notes": {...},
     *     ...
     *   },
     *   "class_id": 5
     * }
     */
    public function saveStructure(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json([ 'success' => false, 'message' => 'Non authentifié' ], 401);
            }

            $teacher = $user->teacher;
            abort_unless($teacher && $teacher->is_prof_principal, 403,
                'Seul un professeur principal peut sauvegarder les structures OCR'
            );

            // Validation robuste des coordonnées de zones
            $validated = $request->validate([
                'field_coordinates' => 'required|array|min:1',
                'field_coordinates.*' => 'array',
                'field_coordinates.*.x' => 'numeric|min:0',
                'field_coordinates.*.y' => 'numeric|min:0',
                'field_coordinates.*.width' => 'numeric|min:1',
                'field_coordinates.*.height' => 'numeric|min:1',
                'class_id' => 'required|integer|exists:classes,id',
            ], [
                'field_coordinates.required' => 'Les coordonnées de zones sont requises',
                'field_coordinates.*.*.numeric' => 'Les coordonnées doivent être des nombres',
                'class_id.required' => 'L\'ID de classe est requis',
                'class_id.exists' => 'La classe n\'existe pas',
            ]);

            // Vérifier que le prof principal gère cette classe
            $class = \App\Models\Classe::findOrFail($validated['class_id']);
            
            // Soit head_teacher_id, soit via l'association many-to-many
            $isHeadTeacher = $class->head_teacher_id === $teacher->id || 
                            $teacher->headClasses()->where('classe_id', $class->id)->exists();
            
            abort_unless($isHeadTeacher, 403,
                'Vous n\'êtes pas autorisé à gérer cette classe'
            );

            // Nettoyer et valider les coordonnées
            $cleanCoordinates = [];
            foreach ($validated['field_coordinates'] as $zoneId => $coords) {
                if (is_array($coords) && !empty($coords)) {
                    $cleanCoordinates[$zoneId] = [
                        'x' => (float) ($coords['x'] ?? 0),
                        'y' => (float) ($coords['y'] ?? 0),
                        'width' => (float) ($coords['width'] ?? 0),
                        'height' => (float) ($coords['height'] ?? 0),
                        'label' => $coords['label'] ?? $zoneId,
                    ];
                }
            }

            if (empty($cleanCoordinates)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Au moins une zone avec des coordonnées valides est requise',
                ], 422);
            }

            // Stocker temporairement en session pour l'étape de vérification
            session([
                'ocr_field_coordinates' => $cleanCoordinates,
                'ocr_class_id' => $validated['class_id'],
                'ocr_zones_saved_at' => now()->toIso8601String(),
            ]);

            \Log::info('OCR: Field coordinates saved to session', [
                'user_id' => auth()->id(),
                'teacher_id' => $teacher->id,
                'class_id' => $validated['class_id'],
                'zones_count' => count($cleanCoordinates),
                'zones' => array_keys($cleanCoordinates),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Coordonnées des zones sauvegardées avec succès. Les données sont prêtes pour la vérification.',
                'zones_saved' => count($cleanCoordinates),
                'zones' => array_keys($cleanCoordinates),
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::warning('OCR saveStructure validation error', [ 'errors' => $e->errors() ]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation: ' . collect($e->errors())->flatten()->first(),
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('OCR Save Structure Error', [
                'error' => $e->getMessage(),
                'class' => get_class($e),
                'user_id' => auth()->id() ?? null,
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Erreur serveur: ' . $e->getMessage(),
            ], 500);
        }
    }
}
