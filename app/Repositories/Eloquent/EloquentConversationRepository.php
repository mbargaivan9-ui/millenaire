<?php

namespace App\Repositories\Eloquent;

use App\Models\Conversation;
use App\Repositories\Interfaces\ConversationRepositoryInterface;
use Illuminate\Pagination\Paginator;

class EloquentConversationRepository implements ConversationRepositoryInterface
{
    /**
     * Obtenir toutes les conversations d'un utilisateur
     */
    public function getUserConversations($userId, int $perPage = 20): Paginator
    {
        return Conversation::query()
            ->whereHas('participants', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->with([
                'participants:id,name,profile_photo',
                'lastMessage.sender:id,name,profile_photo'
            ])
            ->latest('last_message_at')
            ->paginate($perPage);
    }

    /**
     * Obtenir une conversation avec ses participants et dernier message
     */
    public function getConversationWithDetails($conversationId): ?Conversation
    {
        return Conversation::query()
            ->with([
                'participants:id,name,profile_photo,role',
                'lastMessage.sender:id,name,profile_photo',
            ])
            ->find($conversationId);
    }

    /**
     * Créer une nouvelle conversation
     */
    public function create(array $data): Conversation
    {
        return Conversation::create($data);
    }

    /**
     * Ajouter des participants à une conversation
     */
    public function addParticipants($conversationId, array $userIds): void
    {
        $conversation = Conversation::findOrFail($conversationId);
        
        foreach ($userIds as $userId) {
            $conversation->participants()->attach($userId, [
                'unread_count' => 0,
                'last_read_at' => now(),
            ]);
        }
    }

    /**
     * Vérifier si une conversation existe entre deux utilisateurs
     */
    public function getPrivateConversation($userId1, $userId2): ?Conversation
    {
        return Conversation::query()
            ->where('type', 'private')
            ->whereHas('participants', function ($q) use ($userId1) {
                $q->where('user_id', $userId1);
            })
            ->whereHas('participants', function ($q) use ($userId2) {
                $q->where('user_id', $userId2);
            })
            ->first();
    }

    /**
     * Mettre à jour le dernier message
     */
    public function updateLastMessage($conversationId): void
    {
        $lastMessage = Conversation::find($conversationId)
            ->messages()
            ->active()
            ->latest()
            ->first();

        if ($lastMessage) {
            Conversation::find($conversationId)->update([
                'last_message_at' => $lastMessage->created_at,
            ]);
        }
    }
}
