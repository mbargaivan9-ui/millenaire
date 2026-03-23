<?php

/**
 * Admin\DashboardController
 *
 * Tableau de bord administrateur — KPIs, graphiques, activité récente.
 * Phase 3 — Interface Admin
 *
 * @package App\Http\Controllers\Admin
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Mark;
use App\Models\Payment;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index()
    {
        // Cache KPIs for 5 minutes
        $kpis = Cache::remember('admin.kpis', 300, function () {

            $today         = now()->toDateString();
            $thisMonthStart = now()->startOfMonth();

            // Présence aujourd'hui
            $present = (int) \App\Models\Attendance::whereDate('date', $today)->where('status', 'present')->count();
            $absent  = (int) \App\Models\Attendance::whereDate('date', $today)->where('status', 'absent')->count();
            $late    = (int) \App\Models\Attendance::whereDate('date', $today)->where('status', 'late')->count();
            $total   = max(1, $present + $absent + $late);

            // Recettes mensuelles (12 derniers mois)
            $revenueMonthly = [];
            for ($i = 11; $i >= 0; $i--) {
                $month = now()->subMonths($i);
                $revenueMonthly[] = (int) Payment::whereYear('created_at', $month->year)
                    ->whereMonth('created_at', $month->month)
                    ->where('status', 'success')
                    ->sum('amount');
            }

            // Distribution des notes
            $gradeRanges = [
                [0, 9.99], [10, 12], [13, 15], [16, 18], [18.01, 20],
            ];
            $gradeDist = array_map(fn($r) => (int) Mark::whereBetween('score', $r)->count(), $gradeRanges);

            return [
                'students'           => (int) Student::count(),
                'students_trend'     => (int) (Student::whereMonth('created_at', now()->month)->count() - Student::whereMonth('created_at', now()->subMonth()->month)->count()),
                'teachers'           => (int) Teacher::where('is_active', true)->count(),
                'teachers_trend'     => 0,
                'attendance'         => (int) round($present / $total * 100),
                'present'            => (int) $present,
                'absent'             => (int) $absent,
                'late'               => (int) $late,
                'revenue'            => (int) Payment::whereBetween('created_at', [$thisMonthStart, now()])->where('status', 'success')->sum('amount'),
                'revenue_trend'      => 0,
                'revenue_monthly'    => array_map('intval', $revenueMonthly),
                'grade_distribution' => array_map('intval', $gradeDist),
                'payments_paid'      => (int) Payment::where('status', 'success')->whereMonth('created_at', now()->month)->count(),
                'payments_partial'   => (int) Payment::where('status', 'partial')->whereMonth('created_at', now()->month)->count(),
                'payments_pending'   => (int) Payment::where('status', 'pending')->whereMonth('created_at', now()->month)->count(),
            ];
        });

        // Activity log (no cache — real-time)
        $activities = collect();
        if (Schema::hasTable('activity_logs')) {
            $activities = ActivityLog::with('user')
                ->orderByDesc('created_at')
                ->take(15)
                ->get();
        }

        // Alerts
        $alerts = $this->buildAlerts();

        $pageTitle = app()->getLocale() === 'fr' ? 'Tableau de Bord' : 'Dashboard';

        return view('admin.kpi', compact('kpis', 'activities', 'alerts', 'pageTitle'));
    }

    /**
     * Construire les alertes à afficher.
     */
    private function buildAlerts(): array
    {
        $alerts = [];

        // Paiements en retard
        $overduePayments = Payment::where('status', 'pending')
            ->where('created_at', '<', now()->subDays(7))
            ->count();
        if ($overduePayments > 0) {
            $alerts[] = [
                'type'       => 'danger',
                'icon'       => 'alert-circle',
                'title'      => "$overduePayments paiements en retard (>7j)",
                'desc'       => 'Des familles ont des impayés depuis plus d\'une semaine.',
                'action_url' => route('admin.finance.index'),
            ];
        }

        // Absences élevées
        $highAbsences = Student::whereHas('absences', fn($q) => $q->whereMonth('date', now()->month)->where('justified', false), '>=', 5)->count();
        if ($highAbsences > 0) {
            $alerts[] = [
                'type' => 'warning',
                'icon' => 'calendar-x',
                'title' => "$highAbsences élèves avec 5+ absences non justifiées",
                'desc'  => 'Ce mois-ci.',
                'action_url' => route('admin.students.index'),
            ];
        }

        return $alerts;
    }
}
