<?php

namespace App\Repositories\Interfaces;

use App\Models\Teacher;
use Illuminate\Support\Collection;
use Illuminate\Pagination\Paginator;

interface TeacherRepositoryInterface
{
    /**
     * Obtenir tous les enseignants actifs
     */
    public function getActive($perPage = 15): Paginator;

    /**
     * Obtenir un enseignant avec ses relations
     */
    public function getWithRelations($teacherId): ?Teacher;

    /**
     * Obtenir les enseignants sans assignation active
     */
    public function getAvailable(): Collection;

    /**
     * Obtenir les enseignants assignés à une classe
     */
    public function getByClass($classId): Collection;

    /**
     * Chercher enseignants par nom ou prénom
     */
    public function search(string $query, $limit = 10): Collection;

    /**
     * Créer un nouvel enseignant
     */
    public function create(array $data): Teacher;

    /**
     * Mettre à jour un enseignant
     */
    public function update($teacherId, array $data): bool;

    /**
     * Supprimer un enseignant
     */
    public function delete($teacherId): bool;

    /**
     * Compter les enseignants actifs
     */
    public function countActive(): int;
}
