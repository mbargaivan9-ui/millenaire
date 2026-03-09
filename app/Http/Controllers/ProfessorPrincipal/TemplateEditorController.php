<?php

namespace App\Http\Controllers\ProfessorPrincipal;

use App\Http\Controllers\Controller;
use App\Models\BulletinTemplate;
use App\Models\TemplateSubjectAssignment;
use App\Models\Subject;
use App\Models\Classroom;
use App\Services\BulletinCalculatorService;
use App\Jobs\GenerateStudentBulletinsJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TemplateEditorController extends Controller
{
    /**
     * Display list of bulletin templates for current professor principal
     */
    public function index(Request $request)
    {
        $this->authorize('view', BulletinTemplate::class);

        $templates = BulletinTemplate::whereHas('classroom', function ($query) {
            $query->where('professor_principal_id', Auth::id());
        })
            ->with(['classroom', 'creator'])
            ->withCount(['studentBulletins'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('professor-principal.templates.index', [
            'templates' => $templates,
        ]);
    }

    /**
     * Show template details (read-only view)
     */
    public function show(BulletinTemplate $template)
    {
        $this->authorize('view', $template);

        $template->load(['classroom', 'creator', 'subjectAssignments.subject', 'subjectAssignments.teacher']);

        return view('professor-principal.templates.show', [
            'template' => $template,
            'canEdit' => Auth::user()->can('update', $template),
        ]);
    }

    /**
     * Show the editor form for draft templates
     */
    public function edit(BulletinTemplate $template)
    {
        $this->authorize('update', $template);

        // Only draft templates can be edited
        abort_if($template->is_validated, 403, 'Impossible de modifier un modèle validé');

        $template->load([
            'classroom' => fn($q) => $q->with('school'),
            'subjectAssignments' => fn($q) => $q->with(['subject', 'teacher']),
        ]);

        $allSubjects = Subject::orderBy('name')->get();
        $classroomSubjects = $template->classroom?->subjects()->get() ?? collect();

        // Parse template structure for editing
        $templateStructure = json_decode($template->template_json, true) ?? [];

        return view('professor-principal.templates.editor', [
            'template' => $template,
            'templateStructure' => $templateStructure,
            'subjectAssignments' => $template->subjectAssignments,
            'classroomSubjects' => $classroomSubjects,
            'allSubjects' => $allSubjects,
        ]);
    }

    /**
     * Update template draft with editor changes
     */
    public function update(Request $request, BulletinTemplate $template)
    {
        $this->authorize('update', $template);

        abort_if($template->is_validated, 403, 'Impossible de modifier un modèle validé');

        $validated = $request->validate([
            'template_json' => 'required|json|max:10000',
            'html_template' => 'nullable|string|max:50000',
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        try {
            // Validate JSON structure
            $templateData = json_decode($validated['template_json'], true);
            $this->validateTemplateStructure($templateData);

            DB::transaction(function () use ($template, $validated) {
                $template->update([
                    'template_json' => $validated['template_json'],
                    'html_template' => $validated['html_template'] ?? $template->html_template,
                    'name' => $validated['name'] ?? $template->name,
                    'description' => $validated['description'] ?? $template->description,
                    'updated_by' => Auth::id(),
                    'is_validated' => false, // Reset validation on edit
                ]);

                // Log the edit
                activity()
                    ->performedOn($template)
                    ->withProperties(['updated_fields' => array_keys($validated)])
                    ->log('Template draft updated');
            });

            return response()->json([
                'success' => true,
                'message' => 'Modèle mis à jour avec succès',
                'template' => $template->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Validate template structure (JSON schema validation)
     */
    public function validate(Request $request, BulletinTemplate $template)
    {
        $this->authorize('update', $template);

        $validated = $request->validate([
            'template_json' => 'required|json',
        ]);

        try {
            $templateData = json_decode($validated['template_json'], true);
            $validationErrors = $this->validateTemplateStructure($templateData);

            if (!empty($validationErrors)) {
                return response()->json([
                    'success' => false,
                    'errors' => $validationErrors,
                    'message' => 'Le modèle contient des erreurs de structure',
                ], 422);
            }

            // Run additional validation checks
            $additionalErrors = $this->validateTemplateContent($templateData);
            if (!empty($additionalErrors)) {
                return response()->json([
                    'success' => false,
                    'errors' => $additionalErrors,
                    'message' => 'Le modèle contient des données invalides',
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'Modèle validé avec succès',
                'warnings' => $this->getTemplateWarnings($templateData),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Publish/activate template and generate student bulletins
     */
    public function publish(Request $request, BulletinTemplate $template)
    {
        $this->authorize('update', $template);

        abort_if($template->is_validated, 403, 'Ce modèle est déjà publié');

        try {
            DB::transaction(function () use ($template, $request) {
                // Validate template before publishing
                $templateData = json_decode($template->template_json, true);
                $errors = $this->validateTemplateStructure($templateData);
                
                if (!empty($errors)) {
                    throw new \Exception('Le modèle contient des erreurs: ' . implode(', ', $errors));
                }

                // Mark as validated
                $template->update([
                    'is_validated' => true,
                    'validated_at' => now(),
                    'validated_by' => Auth::id(),
                ]);

                // Log publication
                activity()
                    ->performedOn($template)
                    ->log('Template published and activated');

                // Dispatch job to generate student bulletins
                GenerateStudentBulletinsJob::dispatch($template)
                    ->onQueue('default');
            });

            return response()->json([
                'success' => true,
                'message' => 'Modèle publié. Génération des bulletins en cours...',
                'template' => $template->fresh(),
                'redirect' => route('prof-principal.templates.show', $template),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la publication: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Assign subjects and teachers to template
     */
    public function assignSubjects(Request $request, BulletinTemplate $template)
    {
        $this->authorize('update', $template);

        $validated = $request->validate([
            'assignments' => 'required|array',
            'assignments.*.subject_id' => 'required|integer|exists:subjects,id',
            'assignments.*.teacher_id' => 'required|integer|exists:users,id',
            'assignments.*.coefficient' => 'required|numeric|min:0.5|max:10',
        ]);

        try {
            DB::transaction(function () use ($template, $validated) {
                // Clear existing assignments
                $template->subjectAssignments()->delete();

                // Create new assignments
                foreach ($validated['assignments'] as $assignment) {
                    TemplateSubjectAssignment::create([
                        'bulletin_template_id' => $template->id,
                        'subject_id' => $assignment['subject_id'],
                        'teacher_id' => $assignment['teacher_id'],
                        'coefficient' => $assignment['coefficient'],
                        'granted_by' => Auth::id(),
                    ]);
                }

                activity()
                    ->performedOn($template)
                    ->withProperties(['assignments_count' => count($validated['assignments'])])
                    ->log('Subject assignments updated');
            });

            return response()->json([
                'success' => true,
                'message' => count($validated['assignments']) . ' matière(s) assignée(s)',
                'assignments' => $template->subjectAssignments()->with('subject', 'teacher')->get(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'assignation: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Duplicate a template
     */
    public function duplicate(BulletinTemplate $template)
    {
        $this->authorize('update', $template);

        try {
            $duplicated = DB::transaction(function () use ($template) {
                // Create new template
                $newTemplate = $template->replicate();
                $newTemplate->is_validated = false;
                $newTemplate->validated_at = null;
                $newTemplate->validated_by = null;
                $newTemplate->created_by = Auth::id();
                $newTemplate->name = $template->name . ' (Copie)';
                $newTemplate->save();

                // Duplicate subject assignments
                foreach ($template->subjectAssignments as $assignment) {
                    TemplateSubjectAssignment::create([
                        'bulletin_template_id' => $newTemplate->id,
                        'subject_id' => $assignment->subject_id,
                        'teacher_id' => $assignment->teacher_id,
                        'coefficient' => $assignment->coefficient,
                        'granted_by' => Auth::id(),
                    ]);
                }

                activity()
                    ->performedOn($newTemplate)
                    ->withProperties(['original_template_id' => $template->id])
                    ->log('Template duplicated');

                return $newTemplate;
            });

            return response()->json([
                'success' => true,
                'message' => 'Modèle dupliqué avec succès',
                'template' => $duplicated,
                'redirect' => route('prof-principal.templates.edit', $duplicated),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la duplication: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Delete draft template
     */
    public function destroy(BulletinTemplate $template)
    {
        $this->authorize('delete', $template);

        abort_if($template->is_validated, 403, 'Impossible de supprimer un modèle validé');

        try {
            DB::transaction(function () use ($template) {
                $template->subjectAssignments()->delete();
                $template->delete();

                activity()
                    ->performedOn($template)
                    ->log('Template deleted');
            });

            return response()->json([
                'success' => true,
                'message' => 'Modèle supprimé avec succès',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Validate template structure against schema
     */
    private function validateTemplateStructure(array $templateData): array
    {
        $errors = [];

        // Required top-level sections
        $requiredSections = ['header', 'student_info', 'subjects', 'calculations', 'footer'];
        foreach ($requiredSections as $section) {
            if (!isset($templateData[$section])) {
                $errors[] = "Section manquante: $section";
            }
        }

        // Validate header section
        if (isset($templateData['header'])) {
            if (!isset($templateData['header']['school_name'])) {
                $errors[] = "Nom de l'école manquant dans l'en-tête";
            }
        }

        // Validate student_info section
        if (isset($templateData['student_info'])) {
            $requiredFields = ['name', 'matricule', 'classroom'];
            foreach ($requiredFields as $field) {
                if (!isset($templateData['student_info'][$field])) {
                    $errors[] = "Champ étudiant manquant: $field";
                }
            }
        }

        // Validate subjects array
        if (isset($templateData['subjects'])) {
            if (!is_array($templateData['subjects'])) {
                $errors[] = 'Les matières doivent être un tableau';
            } elseif (empty($templateData['subjects'])) {
                $errors[] = 'Au moins une matière est requise';
            } else {
                foreach ($templateData['subjects'] as $idx => $subject) {
                    if (!isset($subject['name'])) {
                        $errors[] = "Matière $idx: nom manquant";
                    }
                    if (!isset($subject['coefficient'])) {
                        $errors[] = "Matière $idx: coefficient manquant";
                    } elseif ($subject['coefficient'] <= 0) {
                        $errors[] = "Matière $idx: coefficient doit être positif";
                    }
                }
            }
        }

        // Validate calculations section
        if (isset($templateData['calculations'])) {
            if (!isset($templateData['calculations']['method'])) {
                $errors[] = "Méthode de calcul non définie";
            }
            if (!in_array($templateData['calculations']['method'] ?? null, ['weighted', 'simple', 'custom'])) {
                $errors[] = "Méthode de calcul invalide";
            }
        }

        return $errors;
    }

    /**
     * Validate template content (data correctness)
     */
    private function validateTemplateContent(array $templateData): array
    {
        $errors = [];

        // Validate coefficients are numeric
        if (isset($templateData['subjects'])) {
            $totalCoeff = 0;
            foreach ($templateData['subjects'] as $idx => $subject) {
                if (!is_numeric($subject['coefficient'] ?? null)) {
                    $errors[] = "Matière {$subject['name']}: coefficient doit être numérique";
                } else {
                    $totalCoeff += $subject['coefficient'];
                }
            }

            if ($totalCoeff <= 0) {
                $errors[] = 'La somme des coefficients doit être positive';
            }
        }

        // Validate appreciation scale if present
        if (isset($templateData['calculations']['appreciation_scale'])) {
            $scale = $templateData['calculations']['appreciation_scale'];
            if (!is_array($scale)) {
                $errors[] = 'Échelle d\'appréciation invalide';
            }
        }

        return $errors;
    }

    /**
     * Get warnings about template configuration
     */
    private function getTemplateWarnings(array $templateData): array
    {
        $warnings = [];

        if (isset($templateData['subjects'])) {
            if (count($templateData['subjects']) > 15) {
                $warnings[] = "Attention: Plus de 15 matières peuvent rendre le bulletin difficile à lire";
            }

            if (count($templateData['subjects']) < 2) {
                $warnings[] = "Attention: Au moins 2 matières sont recommandées";
            }
        }

        if (isset($templateData['calculations']['method'])) {
            if ($templateData['calculations']['method'] === 'custom') {
                $warnings[] = "Assurez-vous que la formule personnalisée est correcte";
            }
        }

        return $warnings;
    }
}
