<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Classes;
use App\Models\Subject;
use App\Models\Announcement;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function dashboard()
    {
        $totalUsers = User::count();
        $totalStudents = User::where('role', 'student')->count();
        $totalClasses = Classes::count();
        $totalSubjects = Subject::count();
        
        // Enrollment trend
        $monthlyEnrollment = DB::table('users')
            ->selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->whereRaw('YEAR(created_at) = YEAR(NOW())')
            ->where('role', 'student')
            ->groupByRaw('MONTH(created_at)')
            ->pluck('count', 'month')->toArray();
        
        // Recent activities
        $recentActivities = User::latest()
            ->where('role', 'student')
            ->take(5)
            ->get();
        
        // Announcements
        $upcomingAnnouncements = Announcement::where('status', 'active')
            ->latest()
            ->take(3)
            ->get();

        return view('admin.dashboard', [
            'totalUsers' => $totalUsers,
            'totalStudents' => $totalStudents,
            'totalClasses' => $totalClasses,
            'totalSubjects' => $totalSubjects,
            'monthlyEnrollment' => $monthlyEnrollment,
            'recentActivities' => $recentActivities,
            'upcomingAnnouncements' => $upcomingAnnouncements
        ]);
    }
}
