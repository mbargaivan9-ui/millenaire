<?php

namespace App\Contracts;

use App\Models\Classe;
use App\Models\User;

/**
 * Interface for managing teacher assignments to classes
 * Follows Interface Segregation Principle
 */
interface TeacherAssignmentServiceInterface
{
    /**
     * Assign a teacher as principal teacher to a class
     */
    public function assignPrincipalTeacher(Classe $classe, User $teacher, ?string $reason = null, User $assignedBy = null): void;

    /**
     * Reassign a principal teacher from one teacher to another
     */
    public function reassignPrincipalTeacher(Classe $classe, User $oldTeacher, User $newTeacher, ?string $reason = null, User $assignedBy = null): void;

    /**
     * Get assignment history for a class
     */
    public function getAssignmentHistory(Classe $classe): array;

    /**
     * Get current assignments for a teacher
     */
    public function getCurrentAssignments(User $teacher): array;

    /**
     * Check if teacher can be assigned to class
     */
    public function canAssign(User $teacher, Classe $classe): bool;
}
