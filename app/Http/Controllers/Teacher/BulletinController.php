<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Bulletin;
use App\Models\Classe;
use App\Models\Mark;
use App\Models\Student;
use App\Models\Subject;
use App\Services\BulletinService;
use App\Services\GradeCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BulletinController extends Controller
{
    public function __construct(
        private readonly BulletinService          $bulletinService,
        private readonly GradeCalculationService  $gradeService,
    ) {}

    /**
     * Display teacher's bulletin assignments (index page).
     *
     * @route GET /teacher/bulletin
     */
    public function index()
    {
        $teacher = Auth::user()->teacher;
        
        // Ensure teacher record exists
        abort_unless($teacher, 403, 'Accès non autorisé. Aucun enregistrement enseignant trouvé.');

        // Get all class-subject-teacher assignments for this teacher
        $assignments = $teacher->classSubjectTeachers()
            ->with(['classe', 'subject'])
            ->get();

        // Check if user is a head teacher (prof principal) and get that class
        $isPrincipal = $teacher->is_prof_principal;
        $principalClass = null;

        if ($isPrincipal) {
            $principalClass = Classe::where('head_teacher_id', $teacher->id)->first();
        }

        return view('teacher.bulletin.index', [
            'assignments'   => $assignments,
            'isPrincipal'   => $isPrincipal,
            'principalClass' => $principalClass,
        ]);
    }

    /**
     * Display the grade entry grid.
     *
     * @route GET /teacher/bulletin/grid/{class_id}
     */
    public function grid(Request $request, int $class_id)
    {
        $term     = (int) $request->get('term', 1);
        $sequence = (int) $request->get('sequence', 1);

        $teacher  = Auth::user()->teacher;
        $class    = Classe::with(['students.user', 'headTeacher'])->findOrFail($class_id);

        // Check teacher has access to this class
        // Access granted if: teacher teaches this subject in this class OR teacher is the head teacher of this class
        $hasAccess = $teacher->classSubjectTeachers()
                            ->where('class_id', $class_id)
                            ->exists()
                        || $class->head_teacher_id === $teacher->id;

        if (!$hasAccess) {
            abort(403, 'Accès non autorisé à cette classe.');
        }

        $students = $class->students()->with('user')->orderBy('last_name')->get();
        $subjects = $class->subjects()->orderBy('name')->get();

        // The teacher's own subject in this class
        $mySubjectPivot = \App\Models\ClassSubjectTeacher::where('teacher_id', $teacher->id)
                            ->where('class_id', $class_id)
                            ->first();

        // Fetch all marks for this class/term/sequence (indexed)
        $allMarks = Mark::where('class_id', $class_id)
                        ->where('term', $term)
                        ->where('sequence', $sequence)
                        ->get()
                        ->groupBy(fn($m) => $m->student_id . '_' . $m->subject_id);

        // Build marks[studentId][subjectId] lookup
        $marks = [];
        foreach ($allMarks as $key => $markCollection) {
            [$sid, $subid] = explode('_', $key);
            $marks[$sid][$subid] = $markCollection->first();
        }

        // Bulletin data (moyennes, rangs)
        $bulletinData = [];
        foreach ($students as $student) {
            $computed = $this->gradeService->computeStudentAverage($student, $class, $term, $sequence);
            $bulletinData[$student->id] = $computed;
        }

        // Completion stats for Prof Principal
        $completion = null;
        if ($teacher->is_prof_principal) {
            $completion = [];
            $totalStudents = $students->count();
            foreach ($subjects as $subject) {
                $filled = Mark::where('class_id', $class_id)
                              ->where('subject_id', $subject->id)
                              ->where('term', $term)
                              ->where('sequence', $sequence)
                              ->whereNotNull('score')
                              ->count();
                $completion[$subject->id] = [
                    'name'   => $subject->name,
                    'filled' => $filled,
                    'total'  => $totalStudents,
                    'pct'    => $totalStudents > 0 ? round($filled / $totalStudents * 100) : 0,
                ];
            }
        }

        return view('teacher.bulletin.grid', [
            'class'           => $class,
            'students'        => $students,
            'subjects'        => $subjects,
            'marks'           => $marks,
            'bulletinData'    => $bulletinData,
            'term'            => $term,
            'sequence'        => $sequence,
            'teacherSubjectId'=> $mySubjectPivot?->subject_id,
            'mySubjectName'   => $mySubjectPivot
                                    ? Subject::find($mySubjectPivot->subject_id)?->name
                                    : null,
            'isProfPrincipal' => $teacher->is_prof_principal,
            'completion'      => $completion,
        ]);
    }

    /**
     * Display bulletin template grid for professor principal.
     * 
     * @route GET /teacher/bulletin/template-grid
     */
    public function templateGrid()
    {
        $user = auth()->user();
        $teacher = $user->teacher;
        
        // Only prof principal can access
        abort_unless($teacher && $teacher->is_prof_principal, 403, 'Accès non autorisé.');

        $classe = $teacher->head_class_id ? Classe::find($teacher->head_class_id) : null;
        
        abort_unless($classe, 403, 'Vous n\'êtes pas assigné à une classe principale.');

        // Get templates for this class
        $templates = \App\Models\BulletinTemplate::where('classe_id', $classe->id)
            ->with('classe')
            ->orderByDesc('created_at')
            ->get();

        // Get subjects for this class
        $subjects = $classe->subjects()->orderBy('name')->get();

        // Get students and bulletins data
        $students = $classe->students()
            ->where('students.is_active', true)
            ->with('user')
            ->join('users', 'students.user_id', '=', 'users.id')
            ->orderBy('users.name')
            ->select('students.*')
            ->get();
        $bulletins = \App\Models\Bulletin::where('class_id', $classe->id)
            ->with('student.user')
            ->orderByDesc('updated_at')
            ->get();

        // Check if template is locked (all bulletins submitted/validated)
        $isLocked = $bulletins->whereNotIn('status', ['draft'])->count() > 0;

        // Get current academic year from settings
        $settings = \App\Models\EstablishmentSetting::first();
        $academicYear = $settings?->academic_year ?? date('Y') . '-' . (date('Y') + 1);

        return view('teacher.bulletin.template-grid', [
            'classe' => $classe,
            'templates' => $templates,
            'students' => $students,
            'subjects' => $subjects,
            'bulletins' => $bulletins,
            'currentTeacher' => $teacher,
            'isLocked' => $isLocked,
            'academicYear' => $academicYear,
            'term' => 1,
            'sequence' => 1,
        ]);
    }

    /**
     * Display OCR wizard for bulletin digitalization.
     *
     * @route GET /teacher/bulletin/ocr-wizard
     */
    public function ocrWizard()
    {
        $user = auth()->user();
        $teacher = $user->teacher;
        
        // Only prof principal or admin can access
        abort_unless($teacher && ($teacher->is_prof_principal || $user->isAdmin()), 403, 'Accès non autorisé.');

        return view('teacher.bulletin.ocr-wizard', [
            'teacher' => $teacher,
            'classId' => 1, // Default class ID for the wizard
        ]);
    }

    /**
     * Get student statistics (API endpoint).
     * 
     * @route GET /teacher/bulletin/api/student/{student}/stats
     */
    public function getStudentStats($studentId)
    {
        $user = auth()->user();
        $teacher = $user->teacher;
        
        // Only prof principal can access
        abort_unless($teacher && $teacher->is_prof_principal, 403, 'Accès non autorisé.');

        $student = Student::find($studentId);
        abort_unless($student && $student->classe_id === $teacher->head_class_id, 403);

        // Simple statistics
        $gradesCount = Mark::where('student_id', $student->id)->count();
        
        return response()->json([
            'grades_count' => $gradesCount,
            'rank' => 'N/A',
        ]);
    }

    /**
     * Get class statistics (API endpoint).
     * 
     * @route GET /teacher/bulletin/api/class/{classe}/stats
     */
    public function getClassStats($classeId)
    {
        $user = auth()->user();
        $teacher = $user->teacher;
        
        // Only prof principal can access
        abort_unless($teacher && $teacher->is_prof_principal, 403, 'Accès non autorisé.');
        abort_unless($teacher->head_class_id === (int)$classeId, 403);

        $classe = Classe::find($classeId);
        $totalStudents = $classe->students()->count();
        
        return response()->json([
            'total_students' => $totalStudents,
            'average_class' => 'N/A',
        ]);
    }

    /**
     * Export all bulletins for a class as ZIP of PDFs.
     *
     * @route GET /teacher/bulletin/{class_id}/export-pdf
     */
    public function exportPdf(int $class_id)
    {
        $teacher = Auth::user()->teacher;
        abort_unless($teacher->is_prof_principal, 403);

        // Dispatch queue job
        \App\Jobs\ExportClassBulletinsJob::dispatch($class_id, auth()->id());

        return back()->with('success', 'Export PDF lancé. Vous recevrez une notification quand le fichier est prêt.');
    }

    /**
     * Send relance notification to teachers who haven't entered grades.
     *
     * @route POST /teacher/bulletin/{class_id}/relance
     */
    public function relance(Request $request, int $class_id)
    {
        $teacher  = Auth::user()->teacher;
        abort_unless($teacher->is_prof_principal, 403);

        $term     = (int) $request->get('term', 1);
        $sequence = (int) $request->get('sequence', 1);

        $class    = Classe::with('subjects.teachers.user')->findOrFail($class_id);
        $students = Student::where('classe_id', $class_id)->count();

        $notified = 0;
        foreach ($class->subjects as $subject) {
            $filled = Mark::where('class_id', $class_id)
                          ->where('subject_id', $subject->id)
                          ->where('term', $term)
                          ->where('sequence', $sequence)
                          ->whereNotNull('score')
                          ->count();

            if ($filled < $students) {
                foreach ($subject->teachers as $subjectTeacher) {
                    $subjectTeacher->user->notify(
                        new \App\Notifications\GradeEntryReminder($class, $subject, $term, $sequence)
                    );
                    $notified++;
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => "$notified notification(s) envoyée(s).",
        ]);
    }

    /**
     * Download the CSV import template for grade entry.
     *
     * @route GET /teacher/bulletin/import-template/{class_id}
     */
    public function importTemplate(int $class_id)
    {
        $students = Student::where('class_id', $class_id)
                           ->with('user')
                           ->orderBy('last_name')
                           ->get();

        $rows = ["matricule,note,nom_eleve"];
        foreach ($students as $s) {
            $rows[] = "{$s->matricule},,\"{$s->full_name}\"";
        }

        $content = implode("\n", $rows);
        return response($content)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', "attachment; filename=template_notes_class{$class_id}.csv");
    }

    /**
     * Lock a class for a given term (Prof Principal only).
     * Once locked, no teacher can modify grades except the prof principal.
     *
     * @route POST /teacher/bulletin/{classe}/lock
     */
    public function lock(Request $request, Classe $classe)
    {
        $teacher = Auth::user()->teacher;
        
        // Only prof principal of this class can lock it
        abort_unless($teacher && $teacher->is_prof_principal && $teacher->head_class_id === $classe->id, 403, 
            'Accès non autorisé. Seul le professeur principal peut verrouiller la classe.');

        $validated = $request->validate([
            'term' => 'required|integer|between:1,3',
            'sequence' => 'required|integer|between:1,3',
            'lock_reason' => 'nullable|string|max:500',
        ]);

        // Create or update the class term lock record
        $lock = \App\Models\ClassTermLock::updateOrCreate(
            [
                'class_id' => $classe->id,
                'term' => $validated['term'],
            ],
            [
                'academic_year' => now()->year,
                'is_locked' => true,
                'locked_by' => auth()->id(),
                'locked_at' => now(),
                'lock_reason' => $validated['lock_reason'] ?? null,
            ]
        );

        // Also lock all bulletin entries for this term/sequence
        \App\Models\BulletinEntry::where('class_id', $classe->id)
            ->where('term', $validated['term'])
            ->where('sequence', $validated['sequence'])
            ->update([
                'is_locked' => true,
                'locked_at' => now(),
                'locked_by' => auth()->id(),
            ]);

        // Log the action
        activity()
            ->causedBy(auth()->user())
            ->performedOn($classe)
            ->withProperties([
                'term' => $validated['term'],
                'lock_reason' => $validated['lock_reason'] ?? null,
            ])
            ->log('Classe verrouillée');

        return back()->with('success', 
            "Classe {$classe->name} verrouillée pour T{$validated['term']}. Aucune modification n'est possible.");
    }

    /**
     * Unlock a class for a given term (Prof Principal only).
     *
     * @route POST /teacher/bulletin/{classe}/unlock
     */
    public function unlock(Request $request, Classe $classe)
    {
        $teacher = Auth::user()->teacher;
        
        // Only prof principal of this class can unlock it
        abort_unless($teacher && $teacher->is_prof_principal && $teacher->head_class_id === $classe->id, 403,
            'Accès non autorisé. Seul le professeur principal peut déverrouiller la classe.');

        $validated = $request->validate([
            'term' => 'required|integer|between:1,3',
        ]);

        // Update the class term lock record
        \App\Models\ClassTermLock::where('class_id', $classe->id)
            ->where('term', $validated['term'])
            ->update(['is_locked' => false]);

        // Unlock all bulletin entries for this term
        \App\Models\BulletinEntry::where('class_id', $classe->id)
            ->where('term', $validated['term'])
            ->update(['is_locked' => false]);

        // Log the action
        activity()
            ->causedBy(auth()->user())
            ->performedOn($classe)
            ->withProperties(['term' => $validated['term']])
            ->log('Classe déverrouillée');

        return back()->with('success', 
            "Classe {$classe->name} déverrouillée pour T{$validated['term']}. Les modifications sont à nouveau possibles.");
    }
}
