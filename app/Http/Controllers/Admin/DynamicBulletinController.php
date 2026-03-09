<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{DynamicBulletinStructure, Classe, User};
use App\Services\{BulletinOCRParser, DynamicBulletinStructureGenerator};
use Illuminate\Http\{Request, Response};
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class DynamicBulletinController extends Controller
{
    private BulletinOCRParser $ocrParser;
    private DynamicBulletinStructureGenerator $structureGenerator;

    public function __construct(
        BulletinOCRParser $ocrParser,
        DynamicBulletinStructureGenerator $structureGenerator
    ) {
        $this->middleware('auth');
        $this->middleware('role:admin,censeur');
        
        $this->ocrParser = $ocrParser;
        $this->structureGenerator = $structureGenerator;
    }

    // ═══════════════════════════════════════════════════════════════════════════
    //  UPLOAD & OCR
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * Afficher le formulaire d'upload
     */
    public function uploadForm(Classe $classe)
    {
        return view('admin.bulletin.ocr.upload', [
            'classe' => $classe,
        ]);
    }

    /**
     * Traiter l'upload et l'OCR
     */
    public function processUpload(Request $request, Classe $classe)
    {
        $request->validate([
            'bulletin_file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120', // 5MB
        ]);

        try {
            // Sauvegarder le fichier
            $file = $request->file('bulletin_file');
            $fileName = 'bulletin_' . time() . '.' . $file->getClientOriginalExtension();
            $filePath = Storage::disk('public')->putFileAs('bulletins/uploads', $file, $fileName);

            // Extraire le texte OpenAI via OCR
            $ocrResult = $this->ocrParser->extractFromFile(
                Storage::disk('public')->path($filePath),
                $file->getClientOriginalExtension()
            );

            // Parser les données OCR
            $parsedData = $this->ocrParser->parseOCRResult($ocrResult);

            // Créer une structure de brouillon
            $structure = $this->structureGenerator->createFromOCRData(
                $classe,
                $parsedData,
                Auth::user(),
                $filePath,
                $file->getClientOriginalExtension() === 'pdf' ? 'pdf' : 'image'
            );

            return redirect()->route('admin.bulletin.review', $structure)->with('success', 'Bulletin analysé avec succès. Vérifiez les données avant validation.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur OCR: ' . $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════════════════════════════════
    //  REVIEW & VALIDATION
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * Vérifier et corriger la structure extraite
     */
    public function review(DynamicBulletinStructure $structure)
    {
        $this->authorize('view', $structure);

        return view('admin.bulletin.ocr.review', [
            'structure' => $structure,
            'fields' => $structure->fields()->ordered()->get(),
            'fullStructure' => $structure->getFullStructure(),
        ]);
    }

    /**
     * Mettre à jour la structure (corrections manuelles)
     */
    public function update(Request $request, DynamicBulletinStructure $structure)
    {
        $this->authorize('update', $structure);

        $validated = $request->validate([
            'structure' => 'array',
            'formula_config' => 'array|nullable',
            'fields' => 'array|nullable',
        ]);

        try {
            // Enregistrer une révision avant les modifications
            $structure->recordRevision(
                Auth::user(),
                'Modifications manuelles de la structure',
                $structure->structure
            );

            // Mettre à jour la structure
            $structure->update($validated);

            // Mettre à jour les champs si fournis
            if (isset($validated['fields'])) {
                foreach ($validated['fields'] as $fieldId => $fieldData) {
                    $field = $structure->fields()->find($fieldId);
                    if ($field) {
                        $field->update($fieldData);
                    }
                }
            }

            return redirect()->back()->with('success', 'Structure mise à jour. Prête à être validée.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Valider la structure (approuver l'OCR)
     */
    public function validateStructure(Request $request, DynamicBulletinStructure $structure)
    {
        $this->authorize('update', $structure);

        $request->validate([
            'validation_notes' => 'nullable|string|max:1000',
        ]);

        try {
            $this->structureGenerator->validate(
                $structure,
                Auth::user(),
                $request->input('validation_notes')
            );

            return redirect()->route('admin.bulletin.index', $structure->classe)->with('success', 'Structure validée avec succès!');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Activer la structure (la passer en production)
     */
    public function activate(DynamicBulletinStructure $structure)
    {
        $this->authorize('update', $structure);

        if ($structure->status !== 'validated') {
            return redirect()->back()->with('error', 'Seules les structures validées peuvent être activées.');
        }

        try {
            $structure->activate();
            
            // Appliquer à tous les bulletins de la classe
            $count = $this->structureGenerator->applyToClassBulletins($structure);

            return redirect()->back()->with('success', "Structure activée et appliquée à $count bulletins.");

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════════════════════════════════
    //  LISTING & HISTORY
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * Lister les structures pour une classe
     */
    public function index(Classe $classe)
    {
        $structures = $classe->dynamicBulletinStructures()
            ->latest()
            ->paginate(15);

        return view('admin.bulletin.ocr.index', [
            'classe' => $classe,
            'structures' => $structures,
        ]);
    }

    /**
     * Afficher l'historique des révisions
     */
    public function history(DynamicBulletinStructure $structure)
    {
        $this->authorize('view', $structure);

        $revisions = $structure->revisions()
            ->with('modifier')
            ->latest('modified_at')
            ->paginate(20);

        return view('admin.bulletin.ocr.history', [
            'structure' => $structure,
            'revisions' => $revisions,
        ]);
    }

    /**
     * Revenir à une version précédente
     */
    public function revertToRevision(DynamicBulletinStructure $structure, int $revisionId)
    {
        $this->authorize('update', $structure);

        try {
            $revision = $structure->revisions()->findOrFail($revisionId);

            // Enregistrer la révision actuelle avant de revenir
            $structure->recordRevision(
                Auth::user(),
                "Retour à la révision du {$revision->modified_at}",
                $structure->structure
            );

            // Revenir à la version précédente
            if ($revision->old_structure) {
                $structure->update([
                    'structure' => $revision->old_structure,
                ]);
            }

            return redirect()->back()->with('success', 'Retour à la version précédente effectué.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════════════════════════════════
    //  EXPORT & PREVIEW
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * Exporter la structure en JSON
     */
    public function export(DynamicBulletinStructure $structure)
    {
        $this->authorize('view', $structure);

        $exportData = $structure->getFullStructure();

        return response()->json($exportData, 200, ['Content-Disposition' => "attachment; filename=\"structure_{$structure->id}.json\""]);
    }

    /**
     * Aperçu de la structure avec exemple de bulletin
     */
    public function preview(DynamicBulletinStructure $structure)
    {
        $this->authorize('view', $structure);

        // Récupérer un étudiant d'exemple
        $students = $structure->classe->students()->with('user')->limit(3)->get();

        return view('admin.bulletin.ocr.preview', [
            'structure' => $structure,
            'fields' => $structure->fields()->ordered()->visible()->get(),
            'exampleStudents' => $students,
        ]);
    }

    // ═══════════════════════════════════════════════════════════════════════════
    //  PDF EXPORT (Phase 9)
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * Download single student bulletin as PDF
     */
    public function downloadBulletinPDF(DynamicBulletinStructure $structure, User $student)
    {
        $this->authorize('view', $structure);

        try {
            $pdfGenerator = app(\App\Services\BulletinPDFGenerator::class);
            return $pdfGenerator->downloadBulletin($structure, $student);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to generate PDF: ' . $e->getMessage());
        }
    }

    /**
     * Download bulk bulletins for class as ZIP
     */
    public function downloadBulkBulletinsPDF(DynamicBulletinStructure $structure)
    {
        $this->authorize('view', $structure);

        try {
            $pdfGenerator = app(\App\Services\BulletinPDFGenerator::class);
            return $pdfGenerator->downloadBulkBulletins($structure);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to generate PDFs: ' . $e->getMessage());
        }
    }

    /**
     * Preview bulletin as HTML
     */
    public function previewBulletinHTML(DynamicBulletinStructure $structure, User $student)
    {
        $this->authorize('view', $structure);

        $pdfGenerator = app(\App\Services\BulletinPDFGenerator::class);
        $html = $pdfGenerator->previewHTML($structure, $student);

        return response($html)->header('Content-Type', 'text/html; charset=utf-8');
    }

    /**
     * Save bulletins to storage
     */
    public function saveBulletinsToStorage(DynamicBulletinStructure $structure)
    {
        $this->authorize('view', $structure);

        try {
            $pdfGenerator = app(\App\Services\BulletinPDFGenerator::class);
            $files = $pdfGenerator->saveBulletinsToStorage($structure);

            return redirect()->back()->with('success', count($files) . ' bulletins saved successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to save bulletins: ' . $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════════════════════════════════
    //  DELETE & ARCHIVE
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * Archiver une structure
     */
    public function archive(DynamicBulletinStructure $structure)
    {
        $this->authorize('delete', $structure);

        $structure->archive();

        return redirect()->back()->with('success', 'Structure archivée.');
    }

    /**
     * Supprimer une structure (draft uniquement)
     */
    public function delete(DynamicBulletinStructure $structure)
    {
        $this->authorize('delete', $structure);

        if ($structure->status !== 'draft') {
            return redirect()->back()->with('error', 'Seules les structures en brouillon peuvent être supprimées.');
        }

        // Supprimer le fichier source
        if (Storage::disk('public')->exists($structure->source_file_path)) {
            Storage::disk('public')->delete($structure->source_file_path);
        }

        $structure->delete();

        return redirect()->back()->with('success', 'Structure supprimée.');
    }
}
