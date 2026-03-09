<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\DashboardMetricsService;
use App\Services\TeamPulseService;
use App\Services\FinanceService;
use App\Services\AnnouncementService;
use Illuminate\View\View;

/**
 * DashboardController - Contrôleur Principal Millénaire
 * 
 * Responsabilité : Acheminer les données du dashboard sans logique métier.
 * La logique métier est déléguée aux services.
 */
class DashboardController extends Controller
{
    /**
     * Injection des dépendances (Service Layer)
     */
    public function __construct(
        private DashboardMetricsService $metricsService,
        private TeamPulseService $teamService,
        private FinanceService $financeService,
        private AnnouncementService $announcementService,
    ) {}

    /**
     * Affiche le dashboard principal
     * 
     * @return \Illuminate\View\View
     */
    public function index(): View
    {
        // 📊 KPI Principaux
        $mainKpis = $this->metricsService->getMainKpis();
        
        // 👥 Pulse Équipe
        $teamPulse = $this->teamService->getPulseData();
        
        // 💰 Flux Financier
        $financeFlow = $this->financeService->getMonthlyFlow(6);
        $recentPayments = $this->financeService->getRecentPayments(5);
        
        // 📢 Données Pipeline
        $enrollmentPipeline = $this->metricsService->getEnrollmentPipeline();
        
        // 📰 Annonces récentes
        $announcements = $this->announcementService->getLatest(3);
        
        // 👨‍🏫 Équipe pédagogique
        $teachers = $this->teamService->getActiveTeachers(6);
        
        // 📈 Distribution par niveau
        $classDistribution = $this->metricsService->getStudentsByLevel();

        return view('dashboard.index', [
            'mainKpis'           => $mainKpis,
            'teamPulse'          => $teamPulse,
            'financeFlow'        => $financeFlow,
            'recentPayments'     => $recentPayments,
            'enrollmentPipeline' => $enrollmentPipeline,
            'announcements'      => $announcements,
            'teachers'           => $teachers,
            'classDistribution'  => $classDistribution,
            'userRole'           => auth()->user()->role,
        ]);
    }

    /**
     * Dashboard simplifié pour enseignant
     * 
     * @return \Illuminate\View\View
     */
    public function teacherDashboard(): View
    {
        $teacher = auth()->user()->teacher;

        return view('dashboard.teacher', [
            'myClasses'     => $teacher->classes,
            'assignments'   => $teacher->getRecentAssignments(5),
            'gradingQueue'  => $teacher->getPendingGrades(),
            'students'      => $teacher->getStudentsByClass(),
        ]);
    }

    /**
     * Dashboard simplifié pour parent
     * 
     * @return \Illuminate\View\View
     */
    public function parentDashboard(): View
    {
        $parent = auth()->user()->guardian;

        return view('dashboard.parent', [
            'children'      => $parent->students,
            'latestGrades'  => $parent->getLatestGrades(),
            'attendance'    => $parent->getAttendanceSummary(),
            'payments'      => $parent->getPaymentHistory(10),
            'announcements' => $this->announcementService->getLatest(5),
        ]);
    }

    /**
     * Dashboard simplifié pour élève
     * 
     * @return \Illuminate\View\View
     */
    public function studentDashboard(): View
    {
        $student = auth()->user()->student;

        return view('dashboard.student', [
            'myGrades'       => $student->getGradesBySubject(),
            'schedule'       => $student->getWeeklySchedule(),
            'assignments'    => $student->getPendingAssignments(),
            'attendance'     => $student->getAttendanceStats(),
            'bulletin'       => $student->getLatestBulletin(),
        ]);
    }

    /**
     * Dashboard comptable (finance)
     * 
     * @return \Illuminate\View\View
     */
    public function accountantDashboard(): View
    {
        return view('dashboard.accountant', [
            'totalsCollected'    => $this->financeService->getTotalCollected(),
            'pendingPayments'    => $this->financeService->getPending(),
            'expensesSummary'    => $this->financeService->getExpensesSummary(),
            'paymentMethodStats' => $this->financeService->getPaymentMethodStats(),
            'receipts'           => $this->financeService->getRecentReceipts(10),
        ]);
    }
}
