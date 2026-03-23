<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Classe;
use App\Models\Mark;
use App\Models\Schedule;
use App\Models\CourseMaterial;
use App\Services\GradeCalculationService;
use Carbon\Carbon;

/**
 * Teacher\DashboardController — Tableau de Bord Enseignant
 */
class DashboardController extends Controller
{
    public function __construct(
        private readonly GradeCalculationService $gradeService
    ) {}

    public function index()
    {
        $user    = auth()->user();
        $teacher = $user->teacher;

        // Classes enseignées
        $classes = Classe::whereHas('classSubjectTeachers', fn($q) => $q->where('teacher_id', $teacher?->id))
            ->orWhere('head_teacher_id', $teacher?->id)
            ->with('students', 'subjects')
            ->withCount('students')
            ->orderBy('name')
            ->get()
            ->unique('id');

        // Total élèves uniques
        $totalStudents = $classes->sum('students_count');

        // Présence aujourd'hui (% sur toutes ses classes)
        $today = now()->toDateString();
        $presentToday = \App\Models\Absence::whereIn('class_id', $classes->pluck('id'))
            ->whereDate('date', $today)
            ->where('status', 'present')
            ->count();
        $totalToday = \App\Models\Absence::whereIn('class_id', $classes->pluck('id'))
            ->whereDate('date', $today)
            ->count();
        $attendanceToday = $totalToday > 0 ? round($presentToday / $totalToday * 100) : 0;

        // Notes à saisir (Prof Principal : élèves sans notes pour séquence en cours)
        $pendingGrades = 0;
        if ($teacher?->head_class_id) {
            $settings     = \App\Models\EstablishmentSetting::getInstance();
            $term         = $settings->current_term ?? 1;
            $sequence     = $settings->current_sequence ?? 1;
            $totalStudents2 = \App\Models\Student::where('classe_id', $teacher->head_class_id)->count();
            $filledGrades = Mark::where(['class_id' => $teacher->head_class_id, 'term' => $term, 'sequence' => $sequence])
                ->whereNotNull('score')
                ->distinct('student_id')
                ->count();
            $pendingGrades = max(0, $totalStudents2 - $filledGrades);
        }

       
        // Emploi du temps du jour
        $todayDow       = strtolower(Carbon::now()->format('l')); // 'monday', 'tuesday', etc.
        $todaySchedule  = Schedule::where('teacher_id', $teacher?->id)
            ->where('day_of_week', $todayDow)
            ->where('is_active', true)
            ->with('subject', 'classe')
            ->orderBy('start_time')
            ->get();

        // 10 dernières notes saisies par cet enseignant
        $recentGrades = Mark::where('teacher_id', $teacher?->id)
            ->with('student.user', 'subject')
            ->orderByDesc('updated_at')
            ->take(10)
            ->get();

        // Bulletins intelligents en attente de publication (pour prof principal)
        // Note: Table report_cards not active yet - use count of 0
        $pendingBulletins = 0;
        
        return view('teacher.dashboard', compact(
            'teacher', 'classes', 'totalStudents',
            'attendanceToday', 'pendingGrades', 'pendingBulletins',
            'todaySchedule', 'recentGrades'
        ));
    }

    /**
     * Display teacher's course materials.
     *
     * @route GET /teacher/courses
     */
    public function courses()
    {
        $user    = auth()->user();
        $teacher = $user->teacher;
        
        // Ensure teacher record exists
        abort_unless($teacher, 403, 'Accès non autorisé. Aucun enregistrement enseignant trouvé.');

        // Get all class-subject-teacher assignments for this teacher
        $assignments = $teacher->classSubjectTeachers()
            ->with(['classe', 'subject'])
            ->get();

        // Get course materials for these assignments
        $materials = CourseMaterial::whereIn(
            'class_subject_teacher_id',
            $assignments->pluck('id')
        )
            ->with(['classSubjectTeacher.subject', 'classSubjectTeacher.classe'])
            ->orderByDesc('upload_date')
            ->paginate(20);

        return view('teacher.courses', [
            'materials' => $materials,
            'assignments' => $assignments,
        ]);
    }

    /**
     * Display teacher's class assignments.
     *
     * @route GET /teacher/assignments
     */
    public function assignments()
    {
        $user    = auth()->user();
        $teacher = $user->teacher;
        
        // Ensure teacher record exists
        abort_unless($teacher, 403, 'Accès non autorisé. Aucun enregistrement enseignant trouvé.');

        // Get all class-subject-teacher assignments for this teacher
        $assignments = $teacher->classSubjectTeachers()
            ->with(['classe.students', 'subject'])
            ->get();

        // Group assignments by class for better display
        $assignmentsByClass = $assignments
            ->groupBy(fn($cst) => $cst->classe->id)
            ->map(function($group) {
                return [
                    'class' => $group->first()->classe,
                    'subjects' => $group->pluck('subject')->unique('id'),
                    'studentCount' => $group->first()->classe?->students()->where('is_active', true)->count() ?? 0,
                ];
            });

        return view('teacher.assignments', [
            'assignments' => $assignments,
            'assignmentsByClass' => $assignmentsByClass,
        ]);
    }

    /**
     * Bulletin/Grades Dashboard for a specific class.
     * 
     * @route GET /teacher/bulletin/{class}
     * @param \App\Models\Classe $class
     */
    public function bulletinDashboard(Classe $class)
    {
        $user    = auth()->user();
        $teacher = $user->teacher;
        
        // Ensure teacher record exists
        abort_unless($teacher, 403, 'Accès non autorisé. Aucun enregistrement enseignant trouvé.');

        // Check if teacher has access to this class
        $hasAccess = $teacher->classSubjectTeachers()
            ->where('class_id', $class->id)
            ->exists() || $teacher->head_class_id === $class->id;
        
        abort_unless($hasAccess, 403, 'Accès refusé à cette classe.');

        // Get class details with students and subjects
        $classDetails = $class->load(['students' => fn($q) => $q->where('is_active', true)]);
        
        // Get settings for current term/sequence
        $settings = \App\Models\EstablishmentSetting::getInstance();
        $term = $settings->current_term ?? 1;
        $sequence = $settings->current_sequence ?? 1;

        // Get marks for this class in current term/sequence
        $marks = Mark::where([
            'class_id' => $class->id,
            'term' => $term,
            'sequence' => $sequence,
        ])->with('student', 'subject', 'teacher')->get();

        // Get subjects for this class
        $subjects = $class->subjects()->get();

        // Get assignments for this class (class-subject-teacher)
        $assignments = $class->classSubjectTeachers()
            ->with('subject', 'teacher')
            ->get();

        return view('teacher.bulletin-dashboard', compact(
            'class', 'classDetails', 'marks', 'subjects', 'assignments', 
            'term', 'sequence', 'teacher'
        ));
    }

    // Report Cards functionality removed
}
