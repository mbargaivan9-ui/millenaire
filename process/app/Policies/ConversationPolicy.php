<?php

namespace App\Policies;

use App\Models\Conversation;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * ConversationPolicy
 *
 * Autorisations pour les opérations sur les conversations.
 */
class ConversationPolicy
{
    use HandlesAuthorization;

    /**
     * L'utilisateur peut voir la conversation s'il en est participant.
     */
    public function view(User $user, Conversation $conversation): bool
    {
        return $conversation->participants->contains($user->id);
    }

    /**
     * L'utilisateur peut modifier la conversation s'il en est le créateur ou admin.
     */
    public function update(User $user, Conversation $conversation): bool
    {
        return $user->isAdmin()
            || $conversation->created_by === $user->id;
    }

    /**
     * Seul un admin ou le créateur peut supprimer.
     */
    public function delete(User $user, Conversation $conversation): bool
    {
        return $user->isAdmin()
            || $conversation->created_by === $user->id;
    }
}
