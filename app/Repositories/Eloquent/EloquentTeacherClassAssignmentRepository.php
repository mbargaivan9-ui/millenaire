<?php

namespace App\Repositories\Eloquent;

use App\Models\TeacherClassAssignment;
use App\Repositories\Interfaces\TeacherClassAssignmentRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Pagination\Paginator;

class EloquentTeacherClassAssignmentRepository implements TeacherClassAssignmentRepositoryInterface
{
    /**
     * Obtenir toutes les assignations actives
     */
    public function getActive($perPage = 20): Paginator
    {
        return TeacherClassAssignment::query()
            ->where('statut', 'actif')
            ->whereNull('date_fin')
            ->with([
                'teacher.user:id,name,profile_photo',
                'classe:id,name,level',
                'assignedBy:id,name',
            ])
            ->latest('date_debut')
            ->paginate($perPage);
    }

    /**
     * Obtenir une assignation avec ses relations
     */
    public function getWithRelations($assignmentId): ?TeacherClassAssignment
    {
        return TeacherClassAssignment::query()
            ->with([
                'teacher.user',
                'classe.students:id,classe_id,user_id',
                'assignedBy:id,name,email',
            ])
            ->find($assignmentId);
    }

    /**
     * Créer une nouvelle assignation
     */
    public function create(array $data): TeacherClassAssignment
    {
        return TeacherClassAssignment::create($data);
    }

    /**
     * Obtenir l'assignation active d'un professeur
     */
    public function getActiveByTeacher($teacherId): ?TeacherClassAssignment
    {
        return TeacherClassAssignment::query()
            ->where('teacher_id', $teacherId)
            ->where('statut', 'actif')
            ->whereNull('date_fin')
            ->with('classe:id,name,level')
            ->latest('date_debut')
            ->first();
    }

    /**
     * Obtenir l'assignation active d'une classe
     */
    public function getActiveByClass($classId): ?TeacherClassAssignment
    {
        return TeacherClassAssignment::query()
            ->where('class_id', $classId)
            ->where('statut', 'actif')
            ->whereNull('date_fin')
            ->with('teacher.user:id,name,profile_photo')
            ->latest('date_debut')
            ->first();
    }

    /**
     * Obtenir l'historique des assignations d'un professeur
     */
    public function getHistoryByTeacher($teacherId, $limit = 10): Collection
    {
        return TeacherClassAssignment::query()
            ->where('teacher_id', $teacherId)
            ->with('classe:id,name', 'assignedBy:id,name')
            ->latest('date_debut')
            ->limit($limit)
            ->get();
    }

    /**
     * Obtenir l'historique des assignations d'une classe
     */
    public function getHistoryByClass($classId, $limit = 10): Collection
    {
        return TeacherClassAssignment::query()
            ->where('class_id', $classId)
            ->with('teacher.user:id,name', 'assignedBy:id,name')
            ->latest('date_debut')
            ->limit($limit)
            ->get();
    }

    /**
     * Archiver une assignation
     */
    public function archive($assignmentId): bool
    {
        return TeacherClassAssignment::find($assignmentId)?->deactivate() ?? false;
    }

    /**
     * Vérifier s'il existe une assignation active
     */
    public function hasActiveAssignment($teacherId, $classId): bool
    {
        return TeacherClassAssignment::query()
            ->where('teacher_id', $teacherId)
            ->where('class_id', $classId)
            ->active()
            ->exists();
    }

    /**
     * Compter les assignations actives
     */
    public function countActive(): int
    {
        return TeacherClassAssignment::where('statut', 'actif')
            ->whereNull('date_fin')
            ->count();
    }
}
