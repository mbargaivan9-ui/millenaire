<?php

namespace App\Policies;

use App\Models\StudentBulletin;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * StudentBulletinPolicy
 * 
 * Control access to student bulletins
 */
class StudentBulletinPolicy
{
    /**
     * View bulletin: professor principal, class teachers, director
     */
    public function view(User $user, StudentBulletin $bulletin): Response
    {
        // Professor principal of the class
        $isProfPrincipal = $bulletin->classroom
            ->teachers()
            ->where('is_principal', true)
            ->where('user_id', $user->id)
            ->exists();

        // Subject teacher assigned to this class
        $isSubjectTeacher = $bulletin->grades()
            ->where('teacher_id', $user->id)
            ->exists();

        // Director or admin
        $isDirectorAdmin = $user->hasRole(['director', 'super_admin']);

        return ($isProfPrincipal || $isSubjectTeacher || $isDirectorAdmin)
            ? Response::allow()
            : Response::deny('Vous n\'avez pas accès à ce bulletin.');
    }

    /**
     * Export bulletin
     */
    public function export(User $user, StudentBulletin $bulletin): Response
    {
        // Only professor principal can export
        $isProfPrincipal = $bulletin->classroom
            ->teachers()
            ->where('is_principal', true)
            ->where('user_id', $user->id)
            ->exists();

        return ($isProfPrincipal || $user->hasRole('director'))
            ? Response::allow()
            : Response::deny('Seul le professeur principal peut exporter les bulletins.');
    }

    /**
     * Lock bulletin
     */
    public function lock(User $user, StudentBulletin $bulletin): Response
    {
        return $user->hasRole('principal_teacher')
            ? Response::allow()
            : Response::deny('Seul le professeur principal peut verrouiller le bulletin.');
    }
}
