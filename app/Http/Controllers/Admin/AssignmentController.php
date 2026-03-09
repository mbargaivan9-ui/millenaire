<?php

/**
 * AssignmentController — Gestion des Affectations Admin (Drag & Drop)
 *
 * Phase 3 — Section 4.2 — Panneau d'Affectations Admin
 * Désignation professeurs principaux, affectation matières/classes,
 * avec traçabilité complète dans teacher_assignment_histories.
 *
 * @package App\Http\Controllers\Admin
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Classe;
use App\Models\Teacher;
use App\Models\TeacherAssignmentHistory;
use App\Models\ClassSubjectTeacher;
use App\Models\Subject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AssignmentController extends Controller
{
    /**
     * Interface principale des affectations — Drag & Drop.
     */
    public function index(): View
    {
        $classes  = Classe::with(['students', 'headTeacher.user'])->orderBy('name')->get();
        $teachers = Teacher::with(['user', 'subjects', 'classes'])
            ->where('is_active', true)
            ->orderBy('id')
            ->get();
        $subjects = Subject::orderBy('name')->get();
        $history  = TeacherAssignmentHistory::with(['oldTeacher.user', 'newTeacher.user', 'class', 'changedBy'])
            ->latest('changed_at')
            ->take(20)
            ->get();

        return view('admin.assignments.index', compact('classes', 'teachers', 'subjects', 'history'));
    }

    /**
     * Affecter un professeur principal à une classe (via AJAX Drag & Drop).
     *
     * @route POST /admin/assignments/set-principal
     */
    public function setPrincipal(Request $request): JsonResponse
    {
        $request->validate([
            'teacher_id' => 'required|exists:teachers,id',
            'class_id'   => 'required|exists:classes,id',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $class     = Classe::findOrFail($request->class_id);
                $newTeacher = Teacher::findOrFail($request->teacher_id);

                // ─── Sauvegarder l'ancienne affectation dans l'historique ─────
                $oldTeacher = Teacher::where('is_prof_principal', true)
                    ->where('head_class_id', $request->class_id)
                    ->first();

                TeacherAssignmentHistory::create([
                    'class_id'       => $request->class_id,
                    'old_teacher_id' => $oldTeacher?->id,
                    'new_teacher_id' => $request->teacher_id,
                    'changed_by'     => auth()->id(),
                    'reason'         => $request->input('reason'),
                    'changed_at'     => now(),
                ]);

                // ─── Retirer l'ancien prof principal ──────────────────────────
                if ($oldTeacher && $oldTeacher->id !== $newTeacher->id) {
                    $oldTeacher->update([
                        'is_prof_principal' => false,
                        'head_class_id'     => null,
                    ]);
                }

                // ─── Affecter le nouveau prof principal ───────────────────────
                $newTeacher->update([
                    'is_prof_principal' => true,
                    'head_class_id'     => $request->class_id,
                ]);

                // ─── Mettre à jour la classe ───────────────────────────────────
                $class->update(['head_teacher_id' => $request->teacher_id]);
            });

            // ─── Log activité ────────────────────────────────────────────────
            activity()
                ->causedBy(auth()->user())
                ->withProperties([
                    'teacher_id' => $request->teacher_id,
                    'class_id'   => $request->class_id,
                ])
                ->log('Professeur principal affecté à une classe');

            $teacher = Teacher::with('user')->find($request->teacher_id);
            $class   = Classe::find($request->class_id);

            return response()->json([
                'success' => true,
                'message' => "Prof. {$teacher->user->name} affecté(e) à {$class->name}",
                'teacher' => [
                    'id'   => $teacher->id,
                    'name' => $teacher->user->display_name ?? $teacher->user->name,
                ],
                'class' => ['id' => $class->id, 'name' => $class->name],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Affectation Matière / Enseignant / Classe.
     *
     * @route POST /admin/assignments/assign-subject
     */
    public function assignSubject(Request $request): JsonResponse
    {
        $request->validate([
            'class_id'   => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
            'teacher_id' => 'required|exists:teachers,id',
        ]);

        // Vérifier si l'affectation existe déjà
        $existing = ClassSubjectTeacher::where([
            'class_id'   => $request->class_id,
            'subject_id' => $request->subject_id,
        ])->first();

        if ($existing) {
            $existing->update(['teacher_id' => $request->teacher_id]);
            $action = 'updated';
        } else {
            ClassSubjectTeacher::create([
                'class_id'   => $request->class_id,
                'subject_id' => $request->subject_id,
                'teacher_id' => $request->teacher_id,
            ]);
            $action = 'created';
        }

        activity()
            ->causedBy(auth()->user())
            ->withProperties($request->only(['class_id', 'subject_id', 'teacher_id']))
            ->log("Affectation matière {$action}");

        return response()->json([
            'success' => true,
            'action'  => $action,
            'message' => 'Affectation enregistrée avec succès',
        ]);
    }

    /**
     * Supprimer une affectation matière.
     *
     * @route DELETE /admin/assignments/unassign-subject
     */
    public function unassignSubject(Request $request): JsonResponse
    {
        $request->validate([
            'class_id'   => 'required|exists:classes,id',
            'subject_id' => 'required|exists:subjects,id',
        ]);

        ClassSubjectTeacher::where([
            'class_id'   => $request->class_id,
            'subject_id' => $request->subject_id,
        ])->delete();

        return response()->json(['success' => true, 'message' => 'Affectation supprimée']);
    }

    /**
     * Obtenir la grille d'affectations pour une classe (API).
     *
     * @route GET /admin/assignments/grid/{class}
     */
    public function getGrid(int $classId): JsonResponse
    {
        $assignments = ClassSubjectTeacher::with(['subject', 'teacher.user'])
            ->where('class_id', $classId)
            ->get()
            ->map(fn($a) => [
                'subject_id'   => $a->subject_id,
                'subject_name' => $a->subject->name,
                'teacher_id'   => $a->teacher_id,
                'teacher_name' => $a->teacher->user->display_name ?? $a->teacher->user->name,
            ]);

        return response()->json(['assignments' => $assignments]);
    }

    /**
     * Historique des affectations.
     */
    public function history(): View
    {
        $history = TeacherAssignmentHistory::with([
            'oldTeacher.user', 'newTeacher.user', 'class', 'changedBy',
        ])
        ->latest('changed_at')
        ->paginate(30);

        return view('admin.assignments.history', compact('history'));
    }
}
