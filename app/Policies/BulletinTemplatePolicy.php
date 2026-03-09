<?php

namespace App\Policies;

use App\Models\BulletinTemplate;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * BulletinTemplatePolicy
 * 
 * Contrôle les permissions sur les templates de bulletins
 */
class BulletinTemplatePolicy
{
    /**
     * Only professor principal who created the template can view it
     */
    public function view(User $user, BulletinTemplate $template): Response
    {
        return $user->id === $template->created_by || $user->hasRole('director')
            ? Response::allow()
            : Response::deny('Vous n\'avez pas accès à ce template.');
    }

    /**
     * Only professor principal who created can edit draft templates
     */
    public function update(User $user, BulletinTemplate $template): Response
    {
        if ($user->id !== $template->created_by && !$user->hasRole('director')) {
            return Response::deny('Vous n\'êtes pas propriétaire de ce template.');
        }

        if ($template->is_validated) {
            return Response::deny('Impossible d\'éditer un template validé. Dupliquez-le d\'abord.');
        }

        return Response::allow();
    }

    /**
     * Only professor principal and director can delete
     */
    public function delete(User $user, BulletinTemplate $template): Response
    {
        return ($user->id === $template->created_by || $user->hasRole('director')) && !$template->is_validated
            ? Response::allow()
            : Response::deny('Impossible de supprimer ce template.');
    }

    /**
     * Validate template
     */
    public function validate(User $user, BulletinTemplate $template): Response
    {
        return $user->id === $template->created_by || $user->hasRole('director')
            ? Response::allow()
            : Response::deny('Vous ne pouvez pas valider ce template.');
    }

    /**
     * Generate student bulletins from template
     */
    public function generate(User $user, BulletinTemplate $template): Response
    {
        return ($user->id === $template->created_by || $user->hasRole('director')) && $template->is_validated
            ? Response::allow()
            : Response::deny('Vous n\'avez pas les permissions pour générer des bulletins.');
    }
}
