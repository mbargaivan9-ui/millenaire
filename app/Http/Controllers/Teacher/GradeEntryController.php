<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\{Classe, ClassSubjectTeacher, Grade, Student, Subject};
use App\Services\GradeService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * GradeEntryController
 *
 * Handles the intuitive grade entry interface with Quick-Find and Focus-Mode
 * Implements the "Living Bulletin" concept with real-time calculations
 */
class GradeEntryController extends Controller
{
    public function __construct(
        private GradeService $gradeService
    ) {}

    /**
     * Show the grade entry interface
     * Displays the living bulletin with overlay inputs
     * OPTIMIZED: Eager loads all required relationships to avoid N+1 queries
     */
    public function index(ClassSubjectTeacher $classSubjectTeacher): View
    {
        // Verify authorization
        if (!$this->isAuthorizedForTeaching($classSubjectTeacher)) {
            abort(403, 'Unauthorized');
        }

        $classe = $classSubjectTeacher->classe;
        $subject = $classSubjectTeacher->subject;
        $teacher = auth()->user()->teacher;

        // OPTIMIZED: Eager load all required relationships
        $students = $classe->students()
            ->where('is_active', true)
            ->with(['guardians', 'user', 'grades' => function ($query) use ($classSubjectTeacher) {
                $query->where('subject_id', $classSubjectTeacher->subject_id)
                      ->where('class_subject_teacher_id', $classSubjectTeacher->id);
            }])
            ->orderBy('user_id')
            ->get()
            ->map(function (Student $student) use ($classSubjectTeacher) {
                // Group grades by term-sequence using the pre-loaded relationship
                $grades = $student->grades
                    ->groupBy(function ($grade) {
                        return $grade->term . '-' . $grade->sequence;
                    });

                return [
                    'id' => $student->id,
                    'matricule' => $student->matricule,
                    'name' => $student->user->name,
                    'email' => $student->user->email,
                    'status' => $student->financial_status,
                    'grades' => $grades,
                ];
            });

        // Get template if exists
        $template = $classe->bulletinTemplates()
            ->where('is_active', true)
            ->first();

        // Get field zone for this subject
        $fieldZone = $template ? $template->getFieldZoneBySubjectId($subject->id) : null;

        return view('teacher.grades.entry', [
            'classSubjectTeacher' => $classSubjectTeacher,
            'classe' => $classe,
            'subject' => $subject,
            'teacher' => $teacher,
            'students' => $students,
            'template' => $template,
            'fieldZone' => $fieldZone,
            'currentTerm' => (int) request('term', 1),
            'currentSequence' => (int) request('sequence', 1),
            'academicYear' => (int) request('year', now()->year),
        ]);
    }

    /**
     * Get students with filter (AJAX)
     */
    public function searchStudents(Request $request, ClassSubjectTeacher $classSubjectTeacher): \Illuminate\Http\JsonResponse
    {
        if (!$this->isAuthorizedForTeaching($classSubjectTeacher)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $search = strtolower($request->input('q', ''));
        $classe = $classSubjectTeacher->classe;

        $students = $classe->students()
            ->where('is_active', true)
            ->with('user')
            ->get()
            ->filter(function (Student $student) use ($search) {
                return empty($search) ||
                    stripos($student->matricule, $search) !== false ||
                    stripos($student->user->name, $search) !== false ||
                    stripos($student->user->email, $search) !== false;
            })
            ->values()
            ->map(function (Student $student) {
                return [
                    'id' => $student->id,
                    'matricule' => $student->matricule,
                    'name' => $student->user->name,
                    'email' => $student->user->email,
                ];
            });

        return response()->json([
            'success' => true,
            'count' => $students->count(),
            'students' => $students,
        ]);
    }

    /**
     * Save a single grade via AJAX
     */
    public function saveGrade(Request $request, ClassSubjectTeacher $classSubjectTeacher): \Illuminate\Http\JsonResponse
    {
        if (!$this->isAuthorizedForTeaching($classSubjectTeacher)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'score' => ['nullable', 'numeric', 'between:0,20'],
            'sequence' => ['required', 'in:1,2,3'],
            'term' => ['required', 'in:1,2,3'],
            'academic_year' => ['required', 'integer'],
            'comments' => ['nullable', 'string', 'max:500'],
            'excused_absence' => ['boolean'],
        ]);

        // Verify student is in this class
        $student = Student::find($validated['student_id']);
        if ($student->classe_id !== $classSubjectTeacher->classe_id) {
            return response()->json(['error' => 'Student not in this class'], 400);
        }

        // Save grade
        $grade = $this->gradeService->recordGrade(
            student: $student,
            classSubjectTeacher: $classSubjectTeacher,
            sequence: $validated['sequence'],
            term: $validated['term'],
            academicYear: $validated['academic_year'],
            score: $validated['score'] ?? null,
            comments: $validated['comments'] ?? null
        );

        if (isset($validated['excused_absence'])) {
            $grade->update(['excused_absence' => $validated['excused_absence']]);
        }

        // Calculate new average
        $subjectAverage = $this->gradeService->calculateSubjectAverage(
            student: $student,
            subject: $classSubjectTeacher->subject,
            term: $validated['term'],
            academicYear: $validated['academic_year']
        );

        $termAverage = $this->gradeService->calculateTermAverage(
            student: $student,
            term: $validated['term'],
            academicYear: $validated['academic_year']
        );

        // Get rank
        $rankings = $this->gradeService->getRankedStudents(
            classeId: $student->classe_id,
            term: $validated['term'],
            academicYear: $validated['academic_year']
        );

        $studentRank = $rankings->firstWhere('student_id', $student->id)?->pluck('rank') ?? null;

        return response()->json([
            'success' => true,
            'message' => 'Grade saved successfully',
            'grade_id' => $grade->id,
            'score' => $grade->score,
            'subject_average' => round($subjectAverage, 2),
            'term_average' => round($termAverage, 2),
            'performance_level' => $this->gradeService->getPerformanceLevel($subjectAverage),
            'passing' => $this->gradeService->isPassing($subjectAverage),
            'rank' => $studentRank,
            'timestamp' => now()->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Get student's current grades and averages
     */
    public function getStudentGrades(Request $request, ClassSubjectTeacher $classSubjectTeacher, Student $student): \Illuminate\Http\JsonResponse
    {
        if (!$this->isAuthorizedForTeaching($classSubjectTeacher)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($student->classe_id !== $classSubjectTeacher->classe_id) {
            return response()->json(['error' => 'Student not in this class'], 400);
        }

        $term = $request->input('term', 1);
        $academicYear = $request->input('year', now()->year);

        $grades = $student->grades()
            ->where('subject_id', $classSubjectTeacher->subject_id)
            ->where('term', $term)
            ->where('academic_year', $academicYear)
            ->with('subject')
            ->get();

        $subjectAverage = $this->gradeService->calculateSubjectAverage(
            student: $student,
            subject: $classSubjectTeacher->subject,
            term: $term,
            academicYear: $academicYear
        );

        return response()->json([
            'success' => true,
            'student_id' => $student->id,
            'student_name' => $student->user->name,
            'matricule' => $student->matricule,
            'subject' => $classSubjectTeacher->subject->name,
            'term' => $term,
            'academic_year' => $academicYear,
            'grades' => $grades->map(fn ($g) => [
                'id' => $g->id,
                'sequence' => $g->sequence,
                'score' => $g->score,
                'comments' => $g->comments,
                'excused_absence' => $g->excused_absence,
            ]),
            'average' => round($subjectAverage, 2),
            'performance_level' => $this->gradeService->getPerformanceLevel($subjectAverage),
            'passing' => $this->gradeService->isPassing($subjectAverage),
        ]);
    }

    /**
     * Get class-wide statistics
     */
    public function getClassStatistics(ClassSubjectTeacher $classSubjectTeacher): \Illuminate\Http\JsonResponse
    {
        if (!$this->isAuthorizedForTeaching($classSubjectTeacher)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $term = (int) request('term', 1);
        $academicYear = (int) request('year', now()->year);

        $stats = $this->gradeService->getClassStatistics(
            classeId: $classSubjectTeacher->classe_id,
            term: $term,
            academicYear: $academicYear
        );

        return response()->json([
            'success' => true,
            'stats' => $stats,
        ]);
    }

    /**
     * Export grades to CSV
     */
    public function exportGrades(ClassSubjectTeacher $classSubjectTeacher): \Illuminate\Http\Response
    {
        if (!$this->isAuthorizedForTeaching($classSubjectTeacher)) {
            abort(403, 'Unauthorized');
        }

        $term = (int) request('term', 1);
        $sequence = (int) request('sequence', 1);
        $academicYear = (int) request('year', now()->year);

        $students = $classSubjectTeacher->classe->students()
            ->where('is_active', true)
            ->with('user')
            ->orderBy('user_id')
            ->get();

        $csv = "Matricule,Name,Score,Comments,Excused Absence\n";

        foreach ($students as $student) {
            $grade = $student->grades()
                ->where('subject_id', $classSubjectTeacher->subject_id)
                ->where('sequence', $sequence)
                ->where('term', $term)
                ->where('academic_year', $academicYear)
                ->first();

            $score = $grade?->score ?? '';
            $comments = $grade?->teacher_comments ?? '';
            $excused = $grade?->excused_absence ? 'Yes' : 'No';

            $csv .= "{$student->matricule},\"{$student->user->name}\",{$score},\"{$comments}\",{$excused}\n";
        }

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename=\"grades_{$classSubjectTeacher->subject->code}_T{$term}_S{$sequence}.csv\"");
    }

    /**
     * Check if user is authorized
     */
    private function isAuthorizedForTeaching(ClassSubjectTeacher $classSubjectTeacher): bool
    {
        if (auth()->user()->isAdmin()) {
            return true;
        }

        $teacher = auth()->user()->teacher;
        if (!$teacher) {
            return false;
        }

        // Teacher can only edit their own teaching assignments
        return $teacher->id === $classSubjectTeacher->teacher_id;
    }
}
