<?php

namespace App\Services;

use App\Models\TeacherClassAssignment;
use App\Repositories\Interfaces\TeacherClassAssignmentRepositoryInterface;
use App\Repositories\Interfaces\TeacherRepositoryInterface;
use App\Repositories\Interfaces\ClassRepositoryInterface;
use Illuminate\Support\Facades\DB;

/**
 * Service pour gérer les assignations de professeurs principaux
 * SOLID - Single Responsibility Principle
 */
class TeacherAssignmentService
{
    public function __construct(
        private TeacherClassAssignmentRepositoryInterface $assignmentRepo,
        private TeacherRepositoryInterface $teacherRepo,
        private ClassRepositoryInterface $classRepo,
    ) {}

    /**
     * Assigner un professeur à une classe
     *
     * @throws \Exception
     */
    public function assign(int $teacherId, int $classId, int $assignedByUserId, ?string $notes = null): TeacherClassAssignment
    {
        return DB::transaction(function () use ($teacherId, $classId, $assignedByUserId, $notes) {
            // Vérifier que le professeur existe
            $teacher = $this->teacherRepo->getWithRelations($teacherId);
            if (!$teacher) {
                throw new \Exception("Professeur introuvable.");
            }

            // Vérifier que la classe existe
            $classe = $this->classRepo->getWithRelations($classId);
            if (!$classe) {
                throw new \Exception("Classe introuvable.");
            }

            // Archiver l'assignation antérieure si elle existe
            if ($teacher->hasActiveAssignment()) {
                $activeAssignment = $teacher->activeAssignment();
                if ($activeAssignment) {
                    $this->assignmentRepo->archive($activeAssignment->id);
                }
            }

            // Créer la nouvelle assignation
            $assignment = $this->assignmentRepo->create([
                'teacher_id' => $teacherId,
                'class_id' => $classId,
                'assigned_by_user_id' => $assignedByUserId,
                'date_debut' => now(),
                'statut' => 'actif',
                'notes' => $notes,
            ]);

            // Dispatcher un événement pour notification
            if (class_exists(\App\Events\TeacherAssignedToClass::class)) {
                event(new \App\Events\TeacherAssignedToClass($assignment));
            }

            return $assignment;
        });
    }

    /**
     * Réassigner un professeur (ancien vers nouveau)
     */
    public function reassign(int $teacherId, int $newClassId, int $assignedByUserId, ?string $reason = null): array
    {
        return DB::transaction(function () use ($teacherId, $newClassId, $assignedByUserId, $reason) {
            // Obtenir l'assignation active
            $oldAssignment = $this->assignmentRepo->getActiveByTeacher($teacherId);
            
            if (!$oldAssignment) {
                throw new \Exception("Le professeur n'a pas d'assignation active.");
            }

            $oldClassId = $oldAssignment->class_id;

            // Archiver l'ancienne assignation
            $this->assignmentRepo->archive($oldAssignment->id);

            // Créer la nouvelle assignation
            $newAssignment = $this->assign($teacherId, $newClassId, $assignedByUserId, $reason);

            // Dispatcher un événement pour traçabilité
            if (class_exists(\App\Events\TeacherReassigned::class)) {
                event(new \App\Events\TeacherReassigned($oldAssignment, $newAssignment, $reason));
            }

            return [
                'old_assignment' => $oldAssignment,
                'new_assignment' => $newAssignment,
                'old_class_id' => $oldClassId,
                'new_class_id' => $newClassId,
            ];
        });
    }

    /**
     * Retirer l'assignation d'un professeur
     */
    public function unassign(int $teacherId): bool
    {
        $activeAssignment = $this->assignmentRepo->getActiveByTeacher($teacherId);
        
        if (!$activeAssignment) {
            return false;
        }

        $this->assignmentRepo->archive($activeAssignment->id);
        
        if (class_exists(\App\Events\TeacherUnassigned::class)) {
            event(new \App\Events\TeacherUnassigned($activeAssignment));
        }

        return true;
    }

    /**
     * Obtenir le historique complet des mutations d'un professeur
     */
    public function getTeacherMutationHistory(int $teacherId): \Illuminate\Support\Collection
    {
        return $this->assignmentRepo->getHistoryByTeacher($teacherId, 50);
    }

    /**
     * Obtenir le historique complet des professeurs principaux d'une classe
     */
    public function getClassTeacherHistory(int $classId): \Illuminate\Support\Collection
    {
        return $this->assignmentRepo->getHistoryByClass($classId, 50);
    }

    /**
     * Vérifier la disponibilité d'un professeur pour assignation
     */
    public function isTeacherAvailable(int $teacherId): bool
    {
        $teacher = $this->teacherRepo->getWithRelations($teacherId);
        
        if (!$teacher) {
            return false;
        }

        return !$teacher->hasActiveAssignment();
    }

    /**
     * Obtenir toutes les assignations actives sous forme de statistiques
     */
    public function getAssignmentStats(): array
    {
        $totalAssignments = $this->assignmentRepo->countActive();
        $totalClasses = $this->classRepo->count();
        $assignedClasses = $totalAssignments;
        $unassignedClasses = max(0, $totalClasses - $assignedClasses);

        return [
            'total_assignments' => $totalAssignments,
            'total_classes' => $totalClasses,
            'assigned_classes' => $assignedClasses,
            'unassigned_classes' => $unassignedClasses,
            'assignment_rate' => $totalClasses > 0 ? round(($assignedClasses / $totalClasses) * 100, 2) : 0,
        ];
    }
}
