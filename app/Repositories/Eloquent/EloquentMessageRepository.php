<?php

namespace App\Repositories\Eloquent;

use App\Models\Message;
use App\Models\MessageReaction;
use App\Repositories\Interfaces\MessageRepositoryInterface;
use Illuminate\Pagination\Paginator;

class EloquentMessageRepository implements MessageRepositoryInterface
{
    /**
     * Obtenir les messages d'une conversation
     */
    public function getMessagesForConversation($conversationId, int $perPage = 50): Paginator
    {
        return Message::query()
            ->where('conversation_id', $conversationId)
            ->with([
                'sender:id,name,profile_photo,role',
                'attachments',
                'reactions.user:id,name'
            ])
            ->active()
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Créer un nouveau message
     */
    public function create(array $data): Message
    {
        return Message::create($data);
    }

    /**
     * Obtenir un message avec ses relations
     */
    public function getWithDetails($messageId): ?Message
    {
        return Message::query()
            ->where('id', $messageId)
            ->with([
                'sender:id,name,profile_photo,role',
                'attachments',
                'reactions.user:id,name'
            ])
            ->first();
    }

    /**
     * Mettre à jour un message
     */
    public function update($messageId, array $data): bool
    {
        return Message::find($messageId)?->update($data) ?? false;
    }

    /**
     * Supprimer un message (soft delete)
     */
    public function delete($messageId): bool
    {
        return Message::find($messageId)?->update([
            'is_deleted' => true,
            'deleted_at' => now(),
            'content' => null,
        ]) ?? false;
    }

    /**
     * Ajouter une réaction à un message
     */
    public function addReaction($messageId, $userId, string $emoji): void
    {
        MessageReaction::updateOrCreate(
            [
                'message_id' => $messageId,
                'user_id' => $userId,
                'emoji' => $emoji,
            ],
            []
        );
    }

    /**
     * Retirer une réaction
     */
    public function removeReaction($messageId, $userId, string $emoji): void
    {
        MessageReaction::where('message_id', $messageId)
            ->where('user_id', $userId)
            ->where('emoji', $emoji)
            ->delete();
    }
}
