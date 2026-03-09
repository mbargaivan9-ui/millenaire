<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Mark;
use App\Models\ClassSubjectTeacher;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MarksController extends Controller
{
    /**
     * Liste des classes du professeur
     */
    public function index(Request $request): \Illuminate\View\View | \Illuminate\Http\RedirectResponse
    {
        $teacher = Auth::user()->teacher;

        // Classes du professeur
        $classesTeaching = ClassSubjectTeacher::where('teacher_id', $teacher->id)
            ->with('classe', 'subject')
            ->get();

        // Sélectionner une classe-matière
        if ($request->filled('class_subject_teacher_id')) {
            $classSubjectTeacher = ClassSubjectTeacher::findOrFail($request->class_subject_teacher_id);

            // Vérifier que le professeur peut accéder à cette classe
            if ($classSubjectTeacher->teacher_id !== $teacher->id) {
                return back()->with('error', 'Accès non autorisé');
            }

            // Récupérer les étudiants et leurs notes
            $students = Student::where('classe_id', $classSubjectTeacher->class_id)
                ->with([
                    'marks' => function ($q) use ($classSubjectTeacher, $request) {
                        $q->where('class_subject_teacher_id', $classSubjectTeacher->id);
                        if ($request->filled('term')) {
                            $q->where('term', $request->term);
                        }
                        if ($request->filled('sequence')) {
                            $q->where('sequence', $request->sequence);
                        }
                    }
                ])
                ->get();

            return view('teacher.marks.input', compact('classSubjectTeacher', 'students'));
        }

        return view('teacher.marks.index', compact('classesTeaching'));
    }

    /**
     * Crée ou met à jour une note
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'class_subject_teacher_id' => 'required|exists:class_subject_teacher,id',
            'term' => 'required|in:Term1,Term2,Term3',
            'sequence' => 'required|in:Seq1,Seq2,Seq3,Seq4',
            'evaluation_type' => 'required|in:continuous,exam,practical',
            'score' => 'required|numeric|min:0|max:20',
            'comment' => 'nullable|string',
        ]);

        // Vérifier les permissions
        $classSubjectTeacher = ClassSubjectTeacher::findOrFail($request->class_subject_teacher_id);
        if ($classSubjectTeacher->teacher_id !== Auth::user()->teacher->id) {
            return back()->with('error', 'Non autorisé');
        }

        Mark::updateOrCreate(
            [
                'student_id' => $validated['student_id'],
                'class_subject_teacher_id' => $validated['class_subject_teacher_id'],
                'term' => $validated['term'],
                'sequence' => $validated['sequence'],
                'evaluation_type' => $validated['evaluation_type'],
            ],
            [
                'score' => $validated['score'],
                'comment' => $validated['comment'],
                'recorded_by' => Auth::user()->name,
                'recorded_at' => now(),
            ]
        );

        // Recalculer les moyennes et rangs
        $this->recalculateAverages($validated['student_id'], $classSubjectTeacher->class_id, $validated['term']);

        return response()->json(['success' => true, 'message' => 'Note enregistrée']);
    }

    /**
     * Marque une saisie multiple
     */
    public function bulkStore(Request $request): \Illuminate\Http\JsonResponse
    {
        $classSubjectTeacher = ClassSubjectTeacher::findOrFail($request->class_subject_teacher_id);

        if ($classSubjectTeacher->teacher_id !== Auth::user()->teacher->id) {
            return back()->with('error', 'Non autorisé');
        }

        foreach ($request->marks as $studentId => $marks) {
            Mark::updateOrCreate(
                [
                    'student_id' => $studentId,
                    'class_subject_teacher_id' => $classSubjectTeacher->id,
                ],
                array_merge($marks, [
                    'recorded_by' => Auth::user()->name,
                    'recorded_at' => now(),
                ])
            );
        }

        return back()->with('success', 'Notes enregistrées');
    }

    /**
     * Recalcule la moyenne et le rang
     */
    private function recalculateAverages($studentId, $classId, $term)
    {
        $student = Student::findOrFail($studentId);

        // Calculer la moyenne du trimestre
        $marks = Mark::whereHas('classSubjectTeacher', function ($q) use ($classId) {
            $q->where('class_id', $classId);
        })
            ->where('student_id', $studentId)
            ->where('term', $term)
            ->get();

        $totalScore = 0;
        $totalCoefficient = 0;

        foreach ($marks as $mark) {
            $totalScore += $mark->score * $mark->coefficient;
            $totalCoefficient += $mark->coefficient;
        }

        $average = $totalCoefficient > 0 ? $totalScore / $totalCoefficient : 0;

        // Calculer le rang
        $allStudentAverages = Student::where('classe_id', $classId)
            ->get()
            ->map(function ($s) use ($term, $classId) {
                $marks = Mark::whereHas('classSubjectTeacher', function ($q) use ($classId) {
                    $q->where('class_id', $classId);
                })
                    ->where('student_id', $s->id)
                    ->where('term', $term)
                    ->get();

                $total = 0;
                $coeff = 0;
                foreach ($marks as $mark) {
                    $total += $mark->score * $mark->coefficient;
                    $coeff += $mark->coefficient;
                }

                return $coeff > 0 ? $total / $coeff : 0;
            })
            ->sortDesc()
            ->values();

        $rank = $allStudentAverages->search($average) + 1;

        // Mettre à jour le bulletin
        ReportCard::updateOrCreate(
            [
                'student_id' => $studentId,
                'class_id' => $classId,
                'term' => $term,
            ],
            [
                'term_average' => $average,
                'rank' => $rank,
            ]
        );
    }
}
