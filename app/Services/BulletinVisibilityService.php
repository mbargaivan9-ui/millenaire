<?php

namespace App\Services;

use App\Models\BulletinNgSession;
use App\Models\BulletinNgSubject;
use Illuminate\Support\Collection;

/**
 * BulletinVisibilityService — Gestion des permissions et visibilité
 * 
 * Service responsable de:
 * - Vérifier si un prof peut voir une session
 * - Récupérer les matières d'un prof dans une session
 * - Vérifier si un prof peut éditer une note pour une matière
 */
class BulletinVisibilityService
{
    /**
     * Vérifier si un enseignant peut voir la session
     * 
     * Un enseignant peut voir si:
     * 1. Il a au moins une matière affiliée à la session
     * 2. La session est rendue visible (visibilite_enseignants = true)
     * 3. La session n'est pas locked (notes_verrouillee = false)
     * 
     * @param int $userId
     * @param int $sessionId
     * @return bool
     */
    public function canTeacherViewSession(int $userId, int $sessionId): bool
    {
        $session = BulletinNgSession::find($sessionId);
        if (! $session) {
            return false;
        }

        // Vérifier si prof a une matière dans cette session
        $hasSubject = BulletinNgSubject::where('config_id', $session->config_id)
            ->where('user_id', $userId)
            ->exists();

        if (! $hasSubject) {
            return false;
        }

        // Vérifier si session visible aux enseignants
        return $session->visibilite_enseignants;
    }

    /**
     * Récupérer les matières d'un prof dans une session
     * 
     * Retourne les BulletinNgSubject où user_id = userId et config_id = session.config_id
     * 
     * @param int $userId
     * @param int $sessionId
     * @return Collection
     */
    public function getTeacherSubjectsInSession(int $userId, int $sessionId): Collection
    {
        $session = BulletinNgSession::find($sessionId);
        if (! $session) {
            return collect();
        }

        return BulletinNgSubject::where('config_id', $session->config_id)
            ->where('user_id', $userId)
            ->get();
    }

    /**
     * Vérifier si enseignant peut saisir une note
     * 
     * Un enseignant peut saisir si:
     * 1. Il a la matière affiliée
     * 2. Session est saisie_ouverte
     * 3. Session n'est pas verrouillée
     * 4. Session visible aux enseignants
     * 
     * @param int $userId
     * @param int $subjectId
     * @param int $sessionId
     * @return bool
     */
    public function canTeacherEditGrade(
        int $userId,
        int $subjectId,
        int $sessionId
    ): bool {
        $session = BulletinNgSession::find($sessionId);
        if (! $session) {
            return false;
        }

        // Session doit être ouverte à la saisie
        if (! $session->isEntryOpen()) {
            return false;
        }

        // Vérifier que prof a cette matière
        $subject = BulletinNgSubject::find($subjectId);
        if (! $subject || $subject->user_id !== $userId) {
            return false;
        }

        // Vérifier que matière fait partie de la config de la session
        if ($subject->config_id !== $session->config_id) {
            return false;
        }

        return true;
    }

    /**
     * Obtenir les sessions visibles pour un enseignant
     * 
     * Retourne toutes les sessions où l'enseignant a au moins une matière
     * ET où visibilite_enseignants = true
     * 
     * @param int $userId
     * @return Collection
     */
    public function getVisibleSessionsForTeacher(int $userId): Collection
    {
        // Récupérer les matières du prof
        $subjects = BulletinNgSubject::where('user_id', $userId)->pluck('config_id')->unique();

        if ($subjects->isEmpty()) {
            return collect();
        }

        // Récupérer les sessions pour ces configs avec visibilité prof
        return BulletinNgSession::whereIn('config_id', $subjects)
            ->where('visibilite_enseignants', true)
            ->where('notes_verrouillee', false)
            ->orderBy('trimestre_number')
            ->orderBy('sequence_number')
            ->get();
    }

    /**
     * Obtenir les configs associées à un prof principal
     * 
     * @param int $userId
     * @return Collection
     */
    public function getProfPrincipalConfigs(int $userId): Collection
    {
        return BulletinNgSession::where('config_id', '!=', null)
            ->with('config')
            ->get()
            ->filter(fn($session) => $session->config && $session->config->prof_principal_id == $userId)
            ->pluck('config')
            ->unique();
    }
}
