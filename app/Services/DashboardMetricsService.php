<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Student;
use App\Models\Classe;
use App\Models\Payment;
use App\Models\Absence;
use App\Models\Grade;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * DashboardMetricsService - Calcul des KPI principaux
 * 
 * Responsabilité : Générer les métriques du dashboard sans polluer le controller
 */
class DashboardMetricsService
{
    /**
     * Récupère les KPI principaux
     * 
     * @return array<string, mixed>
     */
    public function getMainKpis(): array
    {
        return [
            'fraisCollectes' => $this->getMonthlyFeeCollection(),
            'elevesActifs' => $this->getActiveStudents(),
            'inscriptionsMonth' => $this->getMonthlyEnrollments(),
            'tauxPresenceMoyen' => $this->getAverageAttendanceRate(),
            'progressionResultats' => $this->getResultsProgression(),
        ];
    }

    /**
     * Frais collectés ce mois (Équivalent MRR)
     * 
     * @return float
     */
    private function getMonthlyFeeCollection(): float
    {
        return Payment::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->where('status', 'completed')
            ->sum('amount');
    }

    /**
     * Nombre d'élèves actifs (Équivalent Active Users)
     * 
     * @return int
     */
    private function getActiveStudents(): int
    {
        return Student::where('status', 'active')
            ->count();
    }

    /**
     * Inscriptions du mois (Équivalent Orders)
     * 
     * @return int
     */
    private function getMonthlyEnrollments(): int
    {
        return Student::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();
    }

    /**
     * Taux de présence moyen (Équivalent Conversion)
     * 
     * @return float
     */
    private function getAverageAttendanceRate(): float
    {
        $absences = Absence::where('type', '!=', 'justified')
            ->whereMonth('created_at', Carbon::now()->month)
            ->count();
        
        $totalDays = Student::count() * 20; // ~20 jours travaillés
        
        return $totalDays > 0 ? (($totalDays - $absences) / $totalDays) * 100 : 0;
    }

    /**
     * Progression des résultats (QoQ)
     * 
     * @return float
     */
    private function getResultsProgression(): float
    {
        $currentTrimester = Grade::whereMonth('created_at', '>=', Carbon::now()->startOfQuarter()->month)
            ->avg('value') ?? 0;
        
        $previousTrimester = Grade::whereBetween('created_at', [
            Carbon::now()->subQuarters(1)->startOfQuarter(),
            Carbon::now()->subQuarters(1)->endOfQuarter(),
        ])->avg('value') ?? 0;

        return $previousTrimester > 0 
            ? (($currentTrimester - $previousTrimester) / $previousTrimester) * 100 
            : 0;
    }

    /**
     * Pipeline d'inscriptions (Pre/Dossier/Payé/Validé)
     * 
     * @return array<string, int>
     */
    public function getEnrollmentPipeline(): array
    {
        return [
            'pre_inscription' => Student::where('status', 'pending')->count(),
            'dossier_complet' => Student::where('status', 'documents_verified')->count(),
            'paye' => Payment::where('status', 'completed')->distinct('student_id')->count(),
            'valide' => Student::where('status', 'active')->count(),
        ];
    }

    /**
     * Distribution des élèves par niveau (Équivalent Sales by Region)
     * 
     * @return \Illuminate\Support\Collection
     */
    public function getStudentsByLevel(): Collection
    {
        return Classe::with('students')
            ->get()
            ->map(function (Classe $classe) {
                return [
                    'level' => $classe->name,
                    'count' => $classe->students->count(),
                    'percentage' => ($classe->students->count() / Student::count()) * 100,
                ];
            });
    }

    /**
     * Risque d'abandon (absences > 30%)
     * 
     * @return \Illuminate\Support\Collection
     */
    public function getAbandonmentRisk(): Collection
    {
        return Student::withCount('absences')
            ->having('absences_count', '>', 30)
            ->get()
            ->map(function (Student $student) {
                $totalDays = 100;
                $riskScore = ($student->absences_count / $totalDays) * 100;
                return [
                    'student_id' => $student->id,
                    'name' => $student->user->name,
                    'absences' => $student->absences_count,
                    'risk_score' => $riskScore,
                ];
            });
    }

    /**
     * Cotisations mensuelles récurrentes
     * 
     * @return float
     */
    public function getRecurringFees(): float
    {
        return Payment::whereHas('fee', function ($query) {
            $query->where('recurring', true);
        })
        ->where('status', 'completed')
        ->whereMonth('created_at', Carbon::now()->month)
        ->sum('amount');
    }
}
