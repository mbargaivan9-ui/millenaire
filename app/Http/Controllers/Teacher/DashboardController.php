<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Classe;
use App\Models\Mark;
use App\Models\Schedule;
use App\Models\CourseMaterial;
use App\Models\ReportCard;
use App\Services\GradeCalculationService;
use Barryvdh\DomPDF\Facade\Pdf;
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

        // Bulletins non soumis
        $pendingBulletins = 0;
        if ($teacher?->head_class_id) {
            $pendingBulletins = \App\Models\Bulletin::where('class_id', $teacher->head_class_id)
                ->where('status', 'draft')
                ->count();
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
     * Display report cards for professor principal.
     * 
     * @route GET /teacher/report-cards (accessible only to prof_principal)
     */
    public function reportCards()
    {
        $user = auth()->user();
        $teacher = $user->teacher;
        
        // Ensure teacher record exists and is prof principal
        abort_unless($teacher, 403, 'Accès non autorisé. Aucun enregistrement enseignant trouvé.');
        abort_unless($teacher->is_prof_principal, 403, 'Accès non autorisé. Seuls les professeurs principaux peuvent accéder à cette page.');

        // Get head class (the class this professor is responsible for)
        $headClass = $teacher->head_class_id ? Classe::find($teacher->head_class_id) : null;

        if (!$headClass) {
            return view('teacher.report-cards.index', [
                'reportCards' => collect(),
                'headClass' => null,
                'message' => 'Vous n\'êtes pas assigné à une classe principale.'
            ]);
        }

        // Get all report cards for students in the head class
        $reportCards = ReportCard::where('class_id', $headClass->id)
            ->with(['student.user', 'student.classe'])
            ->orderByDesc('term')
            ->orderByDesc('sequence')
            ->paginate(15);

        return view('teacher.report-cards.index', compact('reportCards', 'headClass'));
    }

    /**
     * Show a specific report card.
     * 
     * @route GET /teacher/report-cards/{reportCard}
     */
    public function showReportCard(ReportCard $reportCard)
    {
        $user = auth()->user();
        $teacher = $user->teacher;
        
        // Verify authorization
        abort_unless($teacher, 403, 'Accès non autorisé.');
        abort_unless(
            $teacher->is_prof_principal && $teacher->head_class_id === $reportCard->class_id,
            403,
            'Accès non autorisé à ce bulletin.'
        );

        $reportCard->load('student.user', 'student.classe');

        return view('teacher.report-cards.show', compact('reportCard'));
    }

    /**
     * Edit report card form.
     * 
     * @route GET /teacher/report-cards/{reportCard}/edit
     */
    public function editReportCard(ReportCard $reportCard)
    {
        $user = auth()->user();
        $teacher = $user->teacher;
        
        // Verify authorization
        abort_unless($teacher, 403, 'Accès non autorisé.');
        abort_unless(
            $teacher->is_prof_principal && $teacher->head_class_id === $reportCard->class_id,
            403,
            'Accès non autorisé à ce bulletin.'
        );

        $reportCard->load('student.user', 'student.classe');

        return view('teacher.report-cards.edit', compact('reportCard'));
    }

    /**
     * Update report card.
     * 
     * @route PUT /teacher/report-cards/{reportCard}
     */
    public function updateReportCard(\Illuminate\Http\Request $request, ReportCard $reportCard)
    {
        $user = auth()->user();
        $teacher = $user->teacher;
        
        // Verify authorization
        abort_unless($teacher, 403, 'Accès non autorisé.');
        abort_unless(
            $teacher->is_prof_principal && $teacher->head_class_id === $reportCard->class_id,
            403,
            'Accès non autorisé à ce bulletin.'
        );

        // Validate input
        $validated = $request->validate([
            'appreciation' => 'nullable|string|max:500',
            'behavior_comment' => 'nullable|string|max:500',
        ]);

        // Update report card
        $reportCard->update($validated);

        return redirect()->route('teacher.report-cards.show', $reportCard)
            ->with('success', 'Bulletin mis à jour avec succès.');
    }

    /**
     * Download report card as PDF.
     * 
     * @route GET /teacher/report-cards/{reportCard}/pdf
     */
    public function downloadPDF(ReportCard $reportCard)
    {
        $user = auth()->user();
        $teacher = $user->teacher;
        
        // Verify authorization
        abort_unless($teacher, 403, 'Accès non autorisé.');
        abort_unless(
            $teacher->is_prof_principal && $teacher->head_class_id === $reportCard->class_id,
            403,
            'Accès non autorisé à ce bulletin.'
        );

        $reportCard->load('student.user', 'student.classe');

        // Generate PDF using DomPDF
        $pdf = Pdf::loadView('teacher.report-cards.pdf', compact('reportCard'));

        return $pdf->download("bulletin-{$reportCard->student->user->last_name}-{$reportCard->term}-{$reportCard->sequence}.pdf");
    }
}
