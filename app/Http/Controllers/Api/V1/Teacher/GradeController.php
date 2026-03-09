<?php

/**
 * GradeController — API de Sauvegarde des Notes (AJAX)
 *
 * Phase 4 — Section 5.1.3 — Sauvegarde Prédictive Auto-Save AJAX
 * Endpoint: POST /api/v1/teacher/grades/save
 * Débounce de 800ms côté frontend — sauvegarde silencieuse sans bouton
 *
 * @package App\Http\Controllers\Api\V1\Teacher
 */

namespace App\Http\Controllers\Api\V1\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Mark;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Classe;
use App\Services\GradeCalculationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class GradeController extends Controller
{
    public function __construct(
        private readonly GradeCalculationService $gradeService
    ) {}

    /**
     * Sauvegarder une note individuelle (AJAX debounced).
     *
     * @route POST /api/v1/teacher/grades/save
     */
    public function saveAjax(Request $request): JsonResponse
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'subject_id' => 'required|exists:subjects,id',
            'class_id'   => 'required|exists:classes,id',
            'term'       => 'required|integer|between:1,3',
            'sequence'   => 'required|integer|between:1,3',
            'score'      => 'nullable|numeric|min:0|max:20',
        ]);

        $teacher = auth()->user()->teacher;
        if (!$teacher) {
            return response()->json(['success' => false, 'message' => 'Accès refusé'], 403);
        }

        // Vérifier droits: le prof enseigne cette matière dans cette classe, OU est prof principal
        $canEdit = $teacher->is_prof_principal
            || \App\Models\ClassSubjectTeacher::where([
                'teacher_id' => $teacher->id,
                'subject_id' => $request->subject_id,
                'class_id'   => $request->class_id,
            ])->exists();

        if (!$canEdit) {
            return response()->json(['success' => false, 'message' => 'Non autorisé pour cette matière'], 403);
        }

        // Créer ou mettre à jour la note
        $mark = Mark::updateOrCreate(
            [
                'student_id' => $request->student_id,
                'subject_id' => $request->subject_id,
                'class_id'   => $request->class_id,
                'term'       => $request->term,
                'sequence'   => $request->sequence,
            ],
            [
                'score'      => $request->score,
                'teacher_id' => $teacher->id,
                'updated_by' => auth()->id(),
            ]
        );

        // Recalculer la moyenne et le rang
        $student  = Student::find($request->student_id);
        $class    = Classe::find($request->class_id);
        $computed = $this->gradeService->computeStudentAverage(
            $student, $class, $request->term, $request->sequence
        );

        // Récupérer l'appréciation
        $appreciation = $this->getAppreciation($computed['moyenne'] ?? 0);

        // Logger l'activité
        activity()
            ->causedBy(auth()->user())
            ->performedOn($mark)
            ->withProperties([
                'student_id' => $request->student_id,
                'subject_id' => $request->subject_id,
                'score'      => $request->score,
            ])
            ->log('Note saisie');

        return response()->json([
            'success'      => true,
            'mark_id'      => $mark->id,
            'score'        => $mark->score,
            'moyenne'      => $computed['moyenne'] ?? null,
            'rang'         => $computed['rang'] ?? null,
            'appreciation' => $appreciation['label'],
            'appr_color'   => $appreciation['color'],
        ]);
    }

    /**
     * Sauvegarde de plusieurs notes en batch (Import CSV/Excel).
     *
     * @route POST /api/v1/teacher/grades/batch-save
     */
    public function batchSave(Request $request): JsonResponse
    {
        $request->validate([
            'grades'                => 'required|array|max:200',
            'grades.*.student_id'   => 'required|exists:students,id',
            'grades.*.subject_id'   => 'required|exists:subjects,id',
            'grades.*.class_id'     => 'required|exists:classes,id',
            'grades.*.term'         => 'required|integer|between:1,3',
            'grades.*.sequence'     => 'required|integer|between:1,3',
            'grades.*.score'        => 'nullable|numeric|min:0|max:20',
        ]);

        $teacher  = auth()->user()->teacher;
        $saved    = 0;
        $errors   = [];

        foreach ($request->grades as $idx => $gradeData) {
            try {
                Mark::updateOrCreate(
                    [
                        'student_id' => $gradeData['student_id'],
                        'subject_id' => $gradeData['subject_id'],
                        'class_id'   => $gradeData['class_id'],
                        'term'       => $gradeData['term'],
                        'sequence'   => $gradeData['sequence'],
                    ],
                    [
                        'score'      => $gradeData['score'],
                        'teacher_id' => $teacher->id,
                        'updated_by' => auth()->id(),
                    ]
                );
                $saved++;
            } catch (\Exception $e) {
                $errors[] = "Ligne " . ($idx + 1) . ": " . $e->getMessage();
            }
        }

        return response()->json([
            'success' => $saved > 0,
            'saved'   => $saved,
            'errors'  => $errors,
            'message' => "$saved note(s) importée(s) avec succès",
        ]);
    }

    /**
     * Retourne l'appréciation selon la moyenne.
     */
    private function getAppreciation(float $avg): array
    {
        // Barème configurable depuis Admin Settings
        if ($avg < 10)  return ['label' => 'Insuffisant',  'color' => '#ef4444'];
        if ($avg < 13)  return ['label' => 'Assez Bien',   'color' => '#f59e0b'];
        if ($avg < 16)  return ['label' => 'Bien',          'color' => '#3b82f6'];
        if ($avg < 19)  return ['label' => 'Très Bien',     'color' => '#10b981'];
        return              ['label' => 'Excellent',     'color' => '#8b5cf6'];
    }
}
