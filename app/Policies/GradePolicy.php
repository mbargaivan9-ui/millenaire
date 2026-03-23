<?php

namespace App\Policies;

use App\Models\SmartBulletinGrade;
use App\Models\User;

/**
 * GradePolicy
 *
 * Contrôle granulaire : qui peut modifier quelle note.
 *
 * Règles :
 * - Prof principal de la classe → peut tout modifier
 * - Enseignant → seulement SA matière, bulletin non verrouillé
 * - Admin → lecture seule (publie mais ne saisit pas)
 */
class GradePolicy
{
    public function update(User $user, SmartBulletinGrade $grade): bool
    {
        $bulletin = $grade->bulletin;

        // Bulletin verrouillé ou publié → personne ne peut modifier
        if ($bulletin->isLocked() || $bulletin->isPublished()) {
            return false;
        }

        // Note verrouillée individuellement
        if ($grade->is_locked) {
            return false;
        }

        // Admin ne saisit pas les notes (il publie seulement)
        if ($user->role === 'admin') {
            return false;
        }

        // Prof principal de la classe → peut tout modifier
        if ($this->isPrincipalOfClass($user, $bulletin->class_id)) {
            return true;
        }

        // Enseignant → seulement sa matière
        return $user->role === 'teacher'
            && $user->teacher?->id === $grade->teacher_id;
    }

    public function view(User $user, SmartBulletinGrade $grade): bool
    {
        // Les enseignants de la classe peuvent voir toutes les notes
        if ($user->role === 'admin') return true;
        if ($user->role === 'teacher') {
            return $user->teacher?->classes()
                ->where('classes.id', $grade->bulletin->class_id)
                ->exists() ?? false;
        }
        return false;
    }

    private function isPrincipalOfClass(User $user, int $classId): bool
    {
        return $user->teacher?->classes()
            ->where('classes.id', $classId)
            ->where('teachers.is_prof_principal', true)
            ->exists() ?? false;
    }
}
