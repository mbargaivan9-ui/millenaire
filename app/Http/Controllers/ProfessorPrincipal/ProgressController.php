<?php

namespace App\Http\Controllers\ProfessorPrincipal;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\StudentBulletin;
use App\Models\BulletinGrade;
use Illuminate\Http\Request;

class ProgressController extends Controller
{
    /**
     * Show progress/completion status for a classroom
     * GET /prof-principal/progress/{classroom}
     */
    public function show(Classroom $classroom)
    {
        // Verify authorization
        if ($classroom->prof_principal_id !== auth()->id() && !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized');
        }

        $classroom->load(['students', 'templates']);

        // Get all bulletins for this classroom
        $bulletins = StudentBulletin::where('classroom_id', $classroom->id)
            ->with(['student', 'template', 'grades'])
            ->get();

        // Calculate progress metrics
        $totalBulletins = $bulletins->count();
        $totalStudents = $classroom->students->count();

        $bulletinsByStatus = $bulletins->groupBy('status');
        $statusCounts = [
            'pending' => $bulletinsByStatus->get('pending', collect())->count(),
            'in_progress' => $bulletinsByStatus->get('in_progress', collect())->count(),
            'completed' => $bulletinsByStatus->get('completed', collect())->count(),
            'locked' => $bulletinsByStatus->get('locked', collect())->count(),
        ];

        $completionPercentage = $totalBulletins > 0 
            ? round((($statusCounts['completed'] + $statusCounts['locked']) / $totalBulletins) * 100, 2)
            : 0;

        // Grades completion by subject
        $templates = $classroom->templates;
        $gradeCompletion = [];

        foreach ($templates as $template) {
            $templateGrades = BulletinGrade::whereIn(
                'bulletin_id',
                StudentBulletin::where('template_id', $template->id)->pluck('id')
            )->get();

            $completedGrades = $templateGrades->filter(fn($g) => $g->note_classe !== null && $g->note_composition !== null)->count();

            $gradeCompletion[$template->id] = [
                'template_name' => $template->name ?? 'Template ' . $template->id,
                'total' => $templateGrades->count(),
                'completed' => $completedGrades,
                'percentage' => $templateGrades->count() > 0 
                    ? round(($completedGrades / $templateGrades->count()) * 100, 2)
                    : 0,
            ];
        }

        // Student by completion status
        $studentCompletion = $bulletins->groupBy('student_id')->map(function ($bulletins) {
            $student = $bulletins->first()->student;
            $completed = $bulletins->filter(fn($b) => in_array($b->status, ['completed', 'locked']))->count();
            $total = $bulletins->count();

            return [
                'student' => $student,
                'completed' => $completed,
                'total' => $total,
                'percentage' => $total > 0 ? round(($completed / $total) * 100, 2) : 0,
            ];
        })->values();

        return view('professor-principal.progress', [
            'classroom' => $classroom,
            'bulletins' => $bulletins,
            'totalBulletins' => $totalBulletins,
            'totalStudents' => $totalStudents,
            'statusCounts' => $statusCounts,
            'completionPercentage' => $completionPercentage,
            'gradeCompletion' => $gradeCompletion,
            'studentCompletion' => collect($studentCompletion)->sortByDesc('percentage'),
        ]);
    }
}
