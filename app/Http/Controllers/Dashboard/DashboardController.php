<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Classes;

/**
 * Dashboard Controller Millenaire
 * Gère les différentes vues de tableau de bord
 * SOLID: Single Responsibility
 */
class DashboardController extends Controller
{
    /**
     * Dashboard principal
     */
    public function index()
    {
        $user = auth()->user();
        
        $data = [
            'stats' => $this->getStats($user),
            'recentActivities' => $this->getRecentActivities(),
            'teamMembers' => $this->getTeamMembers(),
            'notifications' => $this->getNotifications($user),
        ];

        return view('dashboard.index', $data);
    }

    private function getStats($user): array
    {
        return [
            'total_users' => User::count(),
            'total_students' => Student::count() ?? 0,
            'total_teachers' => Teacher::count() ?? 0,
            'total_classes' => Classes::count() ?? 0,
        ];
    }

    private function getRecentActivities(): array
    {
        return [
            ['user' => 'Utilisateur', 'action' => 'a accédé au système', 'time' => 'Il y a 5 min'],
            ['user' => 'Admin', 'action' => 'a créé une classe', 'time' => 'Il y a 1 heure'],
        ];
    }

    private function getTeamMembers(): array
    {
        return User::limit(4)->get(['name', 'email', 'profile_photo'])->toArray();
    }

    private function getNotifications($user): array
    {
        return $user->notifications()->limit(5)->get() ?? [];
    }
}
