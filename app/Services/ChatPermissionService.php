<?php

/**
 * ChatPermissionService
 *
 * Matrice des permissions de messagerie entre les rôles.
 * Phase 9 — Messagerie Interne Temps Réel
 *
 * Matrice:
 *   Admin    → Tous
 *   Teacher  → Classes assignées uniquement, autres enseignants
 *   Parent   → Enseignants et Prof Principal seulement
 *   Student  → Autres étudiants de la même classe
 *
 * @package App\Services
 */

namespace App\Services;

use App\Models\User;

class ChatPermissionService
{
    /**
     * Vérifier si user1 peut envoyer un message à user2.
     */
    public function canMessage(User $sender, User $recipient): bool
    {
        // Un utilisateur peut toujours se répondre
        if ($sender->id === $recipient->id) return false;

        $senderRole    = $sender->role;
        $recipientRole = $recipient->role;

        return match ($senderRole) {
            'admin'   => true, // Admin peut contacter tout le monde
            'teacher' => $this->teacherCanMessage($sender, $recipient),
            'parent'  => $this->parentCanMessage($sender, $recipient),
            'student' => $this->studentCanMessage($sender, $recipient),
            default   => false,
        };
    }

    /**
     * Alias pour canMessage
     */
    public function canMessageUser(User $sender, User $recipient): bool
    {
        return $this->canMessage($sender, $recipient);
    }

    /**
     * Vérifier si un enseignant peut contacter le destinataire.
     */
    private function teacherCanMessage(User $teacher, User $recipient): bool
    {
        return match ($recipient->role) {
            'admin'   => true,
            'teacher' => true, // Les enseignants peuvent s'écrire entre eux
            'student' => $this->teacherHasStudentInClass($teacher, $recipient),
            'parent'  => true, // Les enseignants peuvent contacter les parents
            default   => false,
        };
    }

    /**
     * Vérifier si un parent peut contacter le destinataire.
     * Parent → Enseignants, autres parents, Admin
     */
    private function parentCanMessage(User $parent, User $recipient): bool
    {
        return match ($recipient->role) {
            'admin'   => true,
            'teacher' => true, // Parents peuvent contacter les enseignants
            'parent'  => true, // Parents peuvent se contacter mutuellement
            default   => false,
        };
    }

    /**
     * Vérifier si un étudiant peut contacter le destinataire.
     * Student → Autres étudiants de la même classe + Admin
     */
    private function studentCanMessage(User $student, User $recipient): bool
    {
        return match ($recipient->role) {
            'admin'  => true,
            'student' => $this->sameClass($student, $recipient),
            default  => false,
        };
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function teacherHasStudentInClass(User $teacher, User $student): bool
    {
        $teacherModel = $teacher->teacher;
        $studentModel = $student->student;

        if (!$teacherModel || !$studentModel) return false;

        // L'enseignant enseigne-t-il dans la classe de l'étudiant?
        return \App\Models\ClassSubjectTeacher::where('teacher_id', $teacherModel->id)
            ->where('class_id', $studentModel->classe_id)
            ->exists();
    }

    private function teacherHasParentChild(User $teacher, User $parentUser): bool
    {
        $teacherModel = $teacher->teacher;
        $guardian     = $parentUser->guardian;

        if (!$teacherModel || !$guardian) return false;

        // Les enfants de ce parent sont-ils dans les classes de cet enseignant?
        $childClassIds = $guardian->students()->pluck('class_id');

        return \App\Models\ClassSubjectTeacher::where('teacher_id', $teacherModel->id)
            ->whereIn('class_id', $childClassIds)
            ->exists();
    }

    private function parentHasTeacherForChild(User $parentUser, User $teacherUser): bool
    {
        $guardian     = $parentUser->guardian;
        $teacherModel = $teacherUser->teacher;

        if (!$guardian || !$teacherModel) return false;

        $childClassIds = $guardian->students()->pluck('class_id');

        return \App\Models\ClassSubjectTeacher::where('teacher_id', $teacherModel->id)
            ->whereIn('class_id', $childClassIds)
            ->exists();
    }

    private function sameClass(User $user1, User $user2): bool
    {
        $s1 = $user1->student;
        $s2 = $user2->student;

        if (!$s1 || !$s2) return false;

        return $s1->class_id === $s2->class_id;
    }

    /**
     * Récupérer la liste des utilisateurs avec qui $user peut dialoguer.
     */
    public function getAllowedContacts(User $user): \Illuminate\Database\Eloquent\Collection
    {
        return match ($user->role) {
            'admin'   => User::where('id', '!=', $user->id)->where('is_active', true)->get(),
            'teacher' => $this->getTeacherContacts($user),
            'parent'  => $this->getParentContacts($user),
            'student' => $this->getStudentContacts($user),
            default   => collect(),
        };
    }

    /**
     * Rechercher des contacts autorités avec un terme de recherche.
     * 
     * @param User $user Utilisateur qui cherche
     * @param string $search Terme de recherche (nom, prénom, rôle)
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function searchAllowedContacts(User $user, string $search): \Illuminate\Database\Eloquent\Collection
    {
        $contacts = $this->getAllowedContacts($user);

        $searchLower = strtolower($search);

        return $contacts->filter(function ($contact) use ($searchLower) {
            return str_contains(strtolower($contact->name ?? ''), $searchLower)
                || str_contains(strtolower($contact->first_name ?? ''), $searchLower)
                || str_contains(strtolower($contact->last_name ?? ''), $searchLower)
                || str_contains(strtolower($contact->email ?? ''), $searchLower)
                || str_contains(strtolower($contact->role ?? ''), $searchLower);
        })->values();
    }

    private function getTeacherContacts(User $teacher): \Illuminate\Database\Eloquent\Collection
    {
        // Admins + autres enseignants + élèves/parents de ses classes
        $teacherModel = $teacher->teacher;
        if (!$teacherModel) return collect();

        $classIds = \App\Models\ClassSubjectTeacher::where('teacher_id', $teacherModel->id)
            ->pluck('class_id');

        $studentIds = \App\Models\Student::whereIn('classe_id', $classIds)->pluck('user_id');

        return User::where('id', '!=', $teacher->id)
            ->where(function ($q) use ($studentIds) {
                $q->whereIn('role', ['admin', 'teacher'])
                  ->orWhereIn('id', $studentIds);
            })
            ->where('is_active', true)
            ->get();
    }

    private function getParentContacts(User $parentUser): \Illuminate\Database\Eloquent\Collection
    {
        $guardian = $parentUser->guardian;
        if (!$guardian) return collect();

        $childClassIds = $guardian->students()->pluck('class_id');
        $teacherIds    = \App\Models\ClassSubjectTeacher::whereIn('class_id', $childClassIds)
            ->join('teachers', 'teachers.id', '=', 'class_subject_teachers.teacher_id')
            ->pluck('teachers.user_id');

        return User::where('id', '!=', $parentUser->id)
            ->where(function ($q) use ($teacherIds) {
                $q->where('role', 'admin')
                  ->orWhereIn('id', $teacherIds)
                  ->orWhere('role', 'parent'); // Parents peuvent voir les autres parents
            })
            ->where('is_active', true)
            ->get();
    }

    private function getStudentContacts(User $studentUser): \Illuminate\Database\Eloquent\Collection
    {
        $student = $studentUser->student;
        if (!$student) return collect();

        $classmateIds = \App\Models\Student::where('classe_id', $student->classe_id)
            ->where('id', '!=', $student->id)
            ->pluck('user_id');

        return User::where('id', '!=', $studentUser->id)
            ->where(function ($q) use ($classmateIds) {
                $q->where('role', 'admin')
                  ->orWhereIn('id', $classmateIds);
            })
            ->where('is_active', true)
            ->get();
    }
}
