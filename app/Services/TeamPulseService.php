<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Absence;
use App\Models\Discipline;
use App\Models\Teacher;
use App\Models\ActivityLog;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * TeamPulseService - Données de prise de pouls équipe
 * 
 * Responsabilité : Calcul des métriques opérationnelles (demandes, tickets, alertes)
 */
class TeamPulseService
{
    /**
     * Récupère les données de prise de pouls
     * 
     * @return array<string, mixed>
     */
    public function getPulseData(): array
    {
        return [
            'demandesEnAttente' => $this->getPendingAbsences(),
            'delaiTraitementMoyen' => $this->getAverageResolutionTime(),
            'alertesUrgentes' => $this->getCriticalAlerts(),
        ];
    }

    /**
     * Demandes d'absence en attente d'approbation
     * 
     * @return int
     */
    public function getPendingAbsences(): int
    {
        return Absence::where('status', 'pending')
            ->orWhere('status', 'requested')
            ->count();
    }

    /**
     * Délai moyen de traitement des dossiers
     * 
     * @return float (en heures)
     */
    public function getAverageResolutionTime(): float
    {
        return ActivityLog::where('action', 'like', '%treated%')
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->tap(function ($query) {
                $query->selectRaw('TIMEDIFF(updated_at, created_at) as duration');
            })
            ->avg(\DB::raw('EXTRACT(HOUR FROM duration)')) ?? 0;
    }

    /**
     * Alertes disciplinaires urgentes (priorité haute)
     * 
     * @return int
     */
    public function getCriticalAlerts(): int
    {
        return Discipline::where('priority', 'high')
            ->where('status', '!=', 'closed')
            ->count();
    }

    /**
     * Enseignants actifs du mois
     * 
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    public function getActiveTeachers(int $limit = 6): Collection
    {
        return Teacher::withCount('assignments')
            ->with('user')
            ->where('status', 'active')
            ->orderBy('assignments_count', 'desc')
            ->limit($limit)
            ->get()
            ->map(function (Teacher $teacher) {
                return [
                    'id' => $teacher->id,
                    'name' => $teacher->user->name,
                    'assignments' => $teacher->assignments_count,
                    'avatar' => $teacher->user->avatar_url,
                    'status' => 'active',
                ];
            });
    }

    /**
     * Statistiques globales de l'équipe
     * 
     * @return array<string, mixed>
     */
    public function getTeamStats(): array
    {
        $teachers = Teacher::where('status', 'active')->count();
        $activeNow = Teacher::whereHas('activityLog', function ($q) {
            $q->where('created_at', '>=', Carbon::now()->subMinutes(5));
        })->count();

        return [
            'total_teachers' => $teachers,
            'active_now' => $activeNow,
            'response_rate' => $this->calculateResponseRate(),
            'satisfaction' => $this->calculateTeamSatisfaction(),
        ];
    }

    /**
     * Calcule le taux de réponse
     * 
     * @return float
     */
    private function calculateResponseRate(): float
    {
        $total = ActivityLog::where('type', 'request')->count();
        $responded = ActivityLog::where('type', 'response')->count();

        return $total > 0 ? ($responded / $total) * 100 : 0;
    }

    /**
     * Calcule la satisfaction de l'équipe
     * 
     * @return float
     */
    private function calculateTeamSatisfaction(): float
    {
        // Placeholder - À implémenter avec système de notation réel
        return 85.5;
    }
}
