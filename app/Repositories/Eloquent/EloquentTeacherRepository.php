<?php

namespace App\Repositories\Eloquent;

use App\Models\Teacher;
use App\Repositories\Interfaces\TeacherRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Pagination\Paginator;

class EloquentTeacherRepository implements TeacherRepositoryInterface
{
    /**
     * Obtenir tous les enseignants actifs
     */
    public function getActive($perPage = 15): Paginator
    {
        return Teacher::query()
            ->where('is_active', true)
            ->with('user')
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Obtenir un enseignant avec ses relations
     */
    public function getWithRelations($teacherId): ?Teacher
    {
        return Teacher::query()
            ->with([
                'user' => fn($q) => $q->select('id', 'name', 'email', 'profile_photo', 'phoneNumber'),
                'classAssignments' => fn($q) => $q->where('statut', 'actif')->whereNull('date_fin'),
                'activeAssignment.classe',
            ])
            ->find($teacherId);
    }

    /**
     * Obtenir les enseignants sans assignation active
     */
    public function getAvailable(): Collection
    {
        return Teacher::query()
            ->where('is_active', true)
            ->with('user:id,name,profile_photo')
            ->whereDoesntHave('classAssignments', fn($q) => $q
                ->where('statut', 'actif')
                ->whereNull('date_fin')
            )
            ->get();
    }

    /**
     * Obtenir les enseignants assignés à une classe
     */
    public function getByClass($classId): Collection
    {
        return Teacher::query()
            ->with('user')
            ->whereHas('classAssignments', fn($q) => $q
                ->where('class_id', $classId)
                ->where('statut', 'actif')
                ->whereNull('date_fin')
            )
            ->get();
    }

    /**
     * Chercher enseignants par nom ou prénom
     */
    public function search(string $query, $limit = 10): Collection
    {
        return Teacher::query()
            ->where('is_active', true)
            ->with('user')
            ->whereHas('user', fn($q) => $q
                ->where('name', 'like', "%{$query}%")
                ->orWhere('email', 'like', "%{$query}%")
            )
            ->limit($limit)
            ->get();
    }

    /**
     * Créer un nouvel enseignant
     */
    public function create(array $data): Teacher
    {
        return Teacher::create($data);
    }

    /**
     * Mettre à jour un enseignant
     */
    public function update($teacherId, array $data): bool
    {
        return Teacher::find($teacherId)?->update($data) ?? false;
    }

    /**
     * Supprimer un enseignant
     */
    public function delete($teacherId): bool
    {
        return Teacher::destroy($teacherId) > 0;
    }

    /**
     * Compter les enseignants actifs
     */
    public function countActive(): int
    {
        return Teacher::where('is_active', true)->count();
    }
}
