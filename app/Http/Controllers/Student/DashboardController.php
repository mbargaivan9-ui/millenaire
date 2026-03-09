<?php

/**
 * Student\DashboardController
 *
 * Tableau de bord espace étudiant.
 * Phase 7 — Section Étudiant
 *
 * @package App\Http\Controllers\Student
 */

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Bulletin;
use App\Models\CourseMaterial;
use App\Models\Mark;
use App\Models\Quiz;
use App\Models\Subject;
use App\Services\GradeCalculationService;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function __construct(
        private readonly GradeCalculationService $gradeService
    ) {}

    public function index()
    {
        $user    = auth()->user();
        $student = $user->student;

        if (!$student) {
            return view('student.dashboard', ['student' => null]);
        }

        // Dernier bulletin
        $lastBulletin = Bulletin::where('student_id', $student->id)
            ->where('status', 'published')
            ->orderByDesc('term')
            ->orderByDesc('sequence')
            ->first();

        // Notes récentes
        $recentGrades = Mark::where('student_id', $student->id)
            ->with('subject', 'teacher.user')
            ->orderByDesc('updated_at')
            ->take(10)
            ->get();

        // Cours & ressources disponibles
        $recentMaterials = CourseMaterial::whereHas('classes', fn($q) => $q->where('classes.id', $student->class_id))
            ->where('is_published', true)
            ->with('subject', 'teacher.user')
            ->orderByDesc('created_at')
            ->take(6)
            ->get();

        // Quiz disponibles
        $availableQuizzes = Quiz::where('class_id', $student->class_id)
            ->where('is_published', true)
            ->where(fn($q) => $q->whereNull('available_from')->orWhere('available_from', '<=', now()))
            ->where(fn($q) => $q->whereNull('available_until')->orWhere('available_until', '>=', now()))
            ->withCount('questions')
            ->with('subject')
            ->orderByDesc('created_at')
            ->take(4)
            ->get();

        // Absences du mois
        $absencesThisMonth = \App\Models\Absence::where('student_id', $student->id)
            ->whereMonth('date', now()->month)
            ->count();

        // Stats classe pour comparaison
        $classStats = $lastBulletin
            ? $this->gradeService->getClassStats($student->class_id, $lastBulletin->term, $lastBulletin->sequence)
            : null;

        return view('student.dashboard', compact(
            'student',
            'lastBulletin',
            'recentGrades',
            'recentMaterials',
            'availableQuizzes',
            'absencesThisMonth',
            'classStats'
        ));
    }
}
