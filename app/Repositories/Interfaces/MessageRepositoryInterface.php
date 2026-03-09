<?php

namespace App\Repositories\Interfaces;

use App\Models\Message;
use Illuminate\Pagination\Paginator;

interface MessageRepositoryInterface
{
    /**
     * Obtenir les messages d'une conversation
     */
    public function getMessagesForConversation($conversationId, int $perPage = 50): Paginator;

    /**
     * Créer un nouveau message
     */
    public function create(array $data): Message;

    /**
     * Obtenir un message avec ses relations
     */
    public function getWithDetails($messageId): ?Message;

    /**
     * Mettre à jour un message
     */
    public function update($messageId, array $data): bool;

    /**
     * Supprimer un message (soft delete)
     */
    public function delete($messageId): bool;

    /**
     * Ajouter une réaction à un message
     */
    public function addReaction($messageId, $userId, string $emoji): void;

    /**
     * Retirer une réaction
     */
    public function removeReaction($messageId, $userId, string $emoji): void;
}
