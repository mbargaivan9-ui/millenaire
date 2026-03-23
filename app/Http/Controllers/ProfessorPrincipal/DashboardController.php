<?php

namespace App\Http\Controllers\ProfessorPrincipal;

use App\Http\Controllers\Controller;
use App\Models\BulletinTemplate;
use App\Models\StudentBulletin;
use App\Models\Classroom;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Show professor principal dashboard
     * GET /prof-principal/dashboard
     */
    public function show()
    {
        $user = auth()->user();

        // Get all classrooms managed by this prof principal
        $classrooms = Classroom::where('prof_principal_id', $user->id)
            ->with(['students', 'templates'])
            ->paginate(10);

        // Get recent templates
        $recentTemplates = BulletinTemplate::where('created_by', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get statistics
        $totalClassrooms = $classrooms->total();
        $totalBulletins = StudentBulletin::whereIn(
            'classroom_id',
            Classroom::where('prof_principal_id', $user->id)->pluck('id')
        )->count();

        $completedBulletins = StudentBulletin::whereIn(
            'classroom_id',
            Classroom::where('prof_principal_id', $user->id)->pluck('id')
        )->whereIn('status', ['completed', 'locked'])->count();

        $completionPercentage = $totalBulletins > 0 
            ? round(($completedBulletins / $totalBulletins) * 100, 2)
            : 0;

        // Get active templates
        $activeTemplates = BulletinTemplate::where('created_by', $user->id)
            ->where('is_validated', true)
            ->count();

        // Get pending templates
        $pendingTemplates = BulletinTemplate::where('created_by', $user->id)
            ->where('is_validated', false)
            ->count();

        return view('professor-principal.dashboard', [
            'classrooms' => $classrooms,
            'recentTemplates' => $recentTemplates,
            'totalClassrooms' => $totalClassrooms,
            'totalBulletins' => $totalBulletins,
            'completedBulletins' => $completedBulletins,
            'completionPercentage' => $completionPercentage,
            'activeTemplates' => $activeTemplates,
            'pendingTemplates' => $pendingTemplates,
        ]);
    }
}
