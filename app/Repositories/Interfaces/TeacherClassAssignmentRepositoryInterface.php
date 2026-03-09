<?php

namespace App\Repositories\Interfaces;

use App\Models\TeacherClassAssignment;
use Illuminate\Support\Collection;
use Illuminate\Pagination\Paginator;

interface TeacherClassAssignmentRepositoryInterface
{
    /**
     * Obtenir toutes les assignations actives
     */
    public function getActive($perPage = 20): Paginator;

    /**
     * Obtenir une assignation avec ses relations
     */
    public function getWithRelations($assignmentId): ?TeacherClassAssignment;

    /**
     * Créer une nouvelle assignation
     */
    public function create(array $data): TeacherClassAssignment;

    /**
     * Obtenir l'assignation active d'un professeur
     */
    public function getActiveByTeacher($teacherId): ?TeacherClassAssignment;

    /**
     * Obtenir l'assignation active d'une classe
     */
    public function getActiveByClass($classId): ?TeacherClassAssignment;

    /**
     * Obtenir l'historique des assignations d'un professeur
     */
    public function getHistoryByTeacher($teacherId, $limit = 10): Collection;

    /**
     * Obtenir l'historique des assignations d'une classe
     */
    public function getHistoryByClass($classId, $limit = 10): Collection;

    /**
     * Archiver une assignation
     */
    public function archive($assignmentId): bool;

    /**
     * Vérifier s'il existe une assignation active
     */
    public function hasActiveAssignment($teacherId, $classId): bool;

    /**
     * Compter les assignations actives
     */
    public function countActive(): int;
}
