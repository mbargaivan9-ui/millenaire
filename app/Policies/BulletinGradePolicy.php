<?php

namespace App\Policies;

use App\Models\BulletinGrade;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * BulletinGradePolicy
 * 
 * Restricts grade entry to the teacher's assigned subject
 */
class BulletinGradePolicy
{
    /**
     * Teacher can only view grades for their subject
     */
    public function view(User $user, BulletinGrade $grade): Response
    {
        // Check if user is the assigned teacher for this subject
        $isAssigned = $grade->teacher_id === $user->id ||
            $user->hasRole(['director', 'super_admin']);

        return $isAssigned
            ? Response::allow()
            : Response::deny('Vous ne pouvez accéder qu\'aux notes de votre matière.');
    }

    /**
     * Teacher can only update their own grades
     */
    public function update(User $user, BulletinGrade $grade): Response
    {
        // Cannot edit if locked
        if ($grade->is_locked) {
            return Response::deny('Cette note est verrouillée. Modifications imposibles.');
        }

        // Teacher or director
        if ($user->id !== $grade->teacher_id && !$user->hasRole(['director'])) {
            return Response::deny('Vous n\'avez pas les permissions pour modifier cette note.');
        }

        return Response::allow();
    }

    /**
     * Lock grades (professor principal only)
     */
    public function lock(User $user, BulletinGrade $grade): Response
    {
        return $user->hasRole('principal_teacher')
            ? Response::allow()
            : Response::deny('Seul le professeur principal peut verrouiller les notes.');
    }
}
