<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BulletinNgConfig;
use App\Models\Student;
use App\Models\AcademicYear;
use App\Models\Teacher;
use App\Models\Payment;
use App\Models\Mark;
use App\Models\ActivityLog;
use App\Models\Attendance;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class KpiController extends Controller
{
    /**
     * Display KPI dashboard and export options
     */
    public function index()
    {
        try {
            // Use the same KPI data structure as DashboardController
            $today = now()->toDateString();
            $thisMonthStart = now()->startOfMonth();

            // Présence aujourd'hui
            $present = (int) Attendance::whereDate('date', $today)->where('status', 'present')->count();
            $absent = (int) Attendance::whereDate('date', $today)->where('status', 'absent')->count();
            $late = (int) Attendance::whereDate('date', $today)->where('status', 'late')->count();
            $total = max(1, $present + $absent + $late);

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

            // Build KPIs array
            $kpis = [
                'students' => (int) Student::count(),
                'students_trend' => (int) (Student::whereMonth('created_at', now()->month)->count() - Student::whereMonth('created_at', now()->subMonth()->month)->count()),
                'teachers' => (int) Teacher::where('is_active', true)->count(),
                'teachers_trend' => 0,
                'attendance' => (int) round($present / $total * 100),
                'present' => (int) $present,
                'absent' => (int) $absent,
                'late' => (int) $late,
                'revenue' => (int) Payment::whereBetween('created_at', [$thisMonthStart, now()])->where('status', 'success')->sum('amount'),
                'revenue_trend' => 0,
                'revenue_monthly' => array_map('intval', $revenueMonthly),
                'grade_distribution' => array_map('intval', $gradeDist),
                'payments_paid' => (int) Payment::where('status', 'success')->whereMonth('created_at', now()->month)->count(),
                'payments_partial' => (int) Payment::where('status', 'partial')->whereMonth('created_at', now()->month)->count(),
                'payments_pending' => (int) Payment::where('status', 'pending')->whereMonth('created_at', now()->month)->count(),
            ];

            // Get activities
            $activities = collect();
            if (Schema::hasTable('activity_logs')) {
                $activities = ActivityLog::with('user')
                    ->orderByDesc('created_at')
                    ->take(15)
                    ->get();
            }

            // Get bulletin configurations for export options
            $bulletinConfigs = BulletinNgConfig::with(['section', 'academicYear'])
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            // Build alerts
            $alerts = [];
            $inactiveStudents = Student::where('status', 'inactive')->count();
            if ($inactiveStudents > 0) {
                $alerts[] = [
                    'type' => 'info',
                    'icon' => 'info',
                    'title' => "$inactiveStudents élèves inactifs",
                    'desc' => 'En attente d\'activation.',
                    'action_url' => route('admin.students.index'),
                ];
            }

            $pageTitle = app()->getLocale() === 'fr' ? 'Tableau de Bord - Export Bulletins' : 'Dashboard - Bulletin Export';

            return view('admin.kpi', compact('kpis', 'activities', 'alerts', 'bulletinConfigs', 'pageTitle'));
        } catch (\Exception $e) {
            Log::error('KPI Index Error: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors du chargement des données KPI');
        }
    }

    /**
     * Export bulletins as CSV/Excel
     */
    public function export($bulletinConfigId)
    {
        try {
            $bulletinConfig = BulletinNgConfig::findOrFail($bulletinConfigId);
            
            // Check authorization
            if (auth()->user()->cannot('export', $bulletinConfig)) {
                return back()->with('error', 'Non autorisé');
            }

            // Dispatch export job
            \App\Jobs\ExportBulletinsZipJob::dispatch($bulletinConfig, auth()->user());

            return back()->with('success', 'Export des bulletins lancé. Vous recevrez une notification quand il sera prêt.');
        } catch (\Exception $e) {
            Log::error('Bulletin Export Error: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors du démarrage de l\'export');
        }
    }

    /**
     * Export as CSV
     */
    public function exportCsv($term = null, $academicYear = null)
    {
        try {
            // Get bulletins based on filters
            $bulletins = BulletinNgConfig::query();

            if ($term) {
                $bulletins->where('term', $term);
            }

            if ($academicYear) {
                $bulletins->where('academic_year_id', $academicYear);
            }

            $bulletins = $bulletins->get();

            if ($bulletins->isEmpty()) {
                return back()->with('error', 'Aucun bulletin trouvé avec ces critères');
            }

            // Create CSV
            $fileName = 'bulletins_' . now()->format('Y-m-d_H-i-s') . '.csv';
            
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            ];

            $callback = function () use ($bulletins) {
                $file = fopen('php://output', 'w');
                
                // Header
                fputcsv($file, ['Bulletin', 'Classe', 'Terme', 'Année Académique', 'Date Création', 'Statut']);
                
                // Data
                foreach ($bulletins as $bulletin) {
                    fputcsv($file, [
                        $bulletin->name ?? 'N/A',
                        $bulletin->section->name ?? 'N/A',
                        $bulletin->term ?? 'N/A',
                        $bulletin->academicYear->year ?? 'N/A',
                        $bulletin->created_at->format('Y-m-d'),
                        $bulletin->status ?? 'N/A',
                    ]);
                }
                
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            Log::error('CSV Export Error: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors de l\'export CSV');
        }
    }
}
