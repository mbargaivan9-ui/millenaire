<?php

namespace App\Repositories\Eloquent;

use App\Models\Classe;
use App\Repositories\Interfaces\ClassRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Pagination\Paginator;

class EloquentClassRepository implements ClassRepositoryInterface
{
    /**
     * Obtenir toutes les classes actives
     */
    public function getActive($perPage = 20): Paginator
    {
        return Classe::query()
            ->where('is_active', true)
            ->with([
                'profPrincipal:id,name,profile_photo',
                'students:id,classe_id,user_id'
            ])
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Obtenir une classe avec ses relations
     */
    public function getWithRelations($classId): ?Classe
    {
        return Classe::query()
            ->with([
                'profPrincipal:id,name,email,profile_photo',
                'students:id,classe_id,user_id',
                'teacherAssignments' => fn($q) => $q
                    ->where('statut', 'actif')
                    ->whereNull('date_fin'),
                'activeTeacherAssignment.teacher.user',
            ])
            ->find($classId);
    }

    /**
     * Obtenir les classes sans professeur principal
     */
    public function getWithoutPrincipal(): Collection
    {
        return Classe::query()
            ->where('is_active', true)
            ->whereNull('prof_principal_id')
            ->with('students')
            ->get();
    }

    /**
     * Obtenir les classes d'un niveau spécifique
     */
    public function getByLevel(string $level): Collection
    {
        return Classe::query()
            ->where('is_active', true)
            ->where('level', $level)
            ->with('profPrincipal:id,name')
            ->get();
    }

    /**
     * Chercher des classes
     */
    public function search(string $query, $limit = 10): Collection
    {
        return Classe::query()
            ->where('is_active', true)
            ->where('name', 'like', "%{$query}%")
            ->orWhere('level', 'like', "%{$query}%")
            ->limit($limit)
            ->get();
    }

    /**
     * Créer une nouvelle classe
     */
    public function create(array $data): Classe
    {
        return Classe::create($data);
    }

    /**
     * Mettre à jour une classe
     */
    public function update($classId, array $data): bool
    {
        return Classe::find($classId)?->update($data) ?? false;
    }

    /**
     * Supprimer une classe
     */
    public function delete($classId): bool
    {
        return Classe::destroy($classId) > 0;
    }

    /**
     * Compter les classes
     */
    public function count(): int
    {
        return Classe::where('is_active', true)->count();
    }

    /**
     * Obtenir les élèves d'une classe
     */
    public function getStudents($classId): Collection
    {
        return Classe::find($classId)?->students ?? collect();
    }
}
