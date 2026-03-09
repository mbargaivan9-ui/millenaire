<?php

namespace App\Repositories\Interfaces;

use App\Models\Classe;
use Illuminate\Support\Collection;
use Illuminate\Pagination\Paginator;

interface ClassRepositoryInterface
{
    /**
     * Obtenir toutes les classes actives
     */
    public function getActive($perPage = 20): Paginator;

    /**
     * Obtenir une classe avec ses relations
     */
    public function getWithRelations($classId): ?Classe;

    /**
     * Obtenir les classes sans professeur principal
     */
    public function getWithoutPrincipal(): Collection;

    /**
     * Obtenir les classes d'un niveau spécifique
     */
    public function getByLevel(string $level): Collection;

    /**
     * Chercher des classes
     */
    public function search(string $query, $limit = 10): Collection;

    /**
     * Créer une nouvelle classe
     */
    public function create(array $data): Classe;

    /**
     * Mettre à jour une classe
     */
    public function update($classId, array $data): bool;

    /**
     * Supprimer une classe
     */
    public function delete($classId): bool;

    /**
     * Compter les classes
     */
    public function count(): int;

    /**
     * Obtenir les élèves d'une classe
     */
    public function getStudents($classId): Collection;
}
