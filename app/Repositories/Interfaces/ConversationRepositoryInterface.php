<?php

namespace App\Repositories\Interfaces;

use App\Models\Conversation;
use Illuminate\Pagination\Paginator;

interface ConversationRepositoryInterface
{
    /**
     * Obtenir toutes les conversations d'un utilisateur
     */
    public function getUserConversations($userId, int $perPage = 20): Paginator;

    /**
     * Obtenir une conversation avec ses participants et dernier message
     */
    public function getConversationWithDetails($conversationId): ?Conversation;

    /**
     * Créer une nouvelle conversation
     */
    public function create(array $data): Conversation;

    /**
     * Ajouter des participants à une conversation
     */
    public function addParticipants($conversationId, array $userIds): void;

    /**
     * Vérifier si une conversation existe entre deux utilisateurs
     */
    public function getPrivateConversation($userId1, $userId2): ?Conversation;

    /**
     * Mettre à jour le dernier message
     */
    public function updateLastMessage($conversationId): void;
}
