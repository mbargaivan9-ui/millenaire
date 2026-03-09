<?php

namespace App\Services;

use App\Contracts\ChatServiceInterface;
use App\Events\CallInitiated;
use App\Events\MessageEdited;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageReadReceipt;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * ChatService
 * 
 * Implements ChatServiceInterface
 * Manages conversations and messaging for 4 roles
 * Includes message read status, editing, deletion, and WebRTC calls
 */
class ChatService implements ChatServiceInterface
{
    public function __construct(
        private readonly ChatPermissionService $permissions,
        private readonly MessageService $messageService
    ) {}

    /**
     * Send a message in a conversation
     */
    public function sendMessage(
        Conversation $conversation,
        User $sender,
        ?string $content = null,
        string $type = 'text'
    ): Message {
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'user_id'         => $sender->id,
            'sender_id'       => $sender->id,
            'body'            => $content,
            'content'         => $content,
            'type'            => $type,
        ]);

        // Increment unread count for other participants
        $this->messageService->incrementUnreadCount($conversation, $sender->id);

        // Update conversation last_message_at
        $conversation->update(['last_message_at' => now()]);

        return $message;
    }

    /**
     * Edit a message
     */
    public function editMessage(Message $message, User $user, string $newContent): Message
    {
        // Verify the user is the message author
        if ($message->user_id !== $user->id) {
            throw new \Exception('Unauthorized to edit this message');
        }

        $message->update([
            'body' => $newContent,
            'content' => $newContent,
            'is_edited' => true,
            'edited_at' => now(),
        ]);

        // Broadcast the edit event
        event(new MessageEdited($message));

        return $message;
    }

    /**
     * Delete message for sender only
     */
    public function deleteMessageForSender(Message $message, User $user): void
    {
        if ($message->user_id !== $user->id) {
            throw new \Exception('Unauthorized to delete this message');
        }

        $message->update(['is_deleted_for_sender' => true]);
    }

    /**
     * Delete message for everyone
     */
    public function deleteMessageForAll(Message $message, User $user): void
    {
        if ($message->user_id !== $user->id) {
            throw new \Exception('Unauthorized to delete this message');
        }

        $message->update([
            'is_deleted_for_all' => true,
            'body' => null,
            'content' => null,
        ]);

        // Broadcast the deletion
        event(new MessageEdited($message));
    }

    /**
     * Mark message as read by user
     */
    public function markMessageAsRead(Message $message, User $user): void
    {
        // Check if read receipt already exists
        if (!MessageReadReceipt::where('message_id', $message->id)
            ->where('user_id', $user->id)
            ->exists()) {
            
            MessageReadReceipt::create([
                'message_id' => $message->id,
                'user_id' => $user->id,
                'read_at' => now(),
            ]);
        }
    }

    /**
     * Get read status of a message
     */
    public function getReadStatus(Message $message, Conversation $conversation): array
    {
        $readers = MessageReadReceipt::where('message_id', $message->id)
            ->with('user')
            ->get();

        $readByCount = $readers->count();
        $totalParticipants = $conversation->participants->count() - 1; // Excluding sender

        return [
            'read_count' => $readByCount,
            'total_participants' => $totalParticipants,
            'readers' => $readers->map(fn($r) => [
                'user_id' => $r->user_id,
                'user_name' => $r->user->name,
                'read_at' => $r->read_at,
            ]),
            'status' => match(true) {
                $readByCount === 0 => 'sent',     // ✓✓ grey
                $readByCount > 0 => 'read',       // ✓✓ green
                default => 'sent'
            }
        ];
    }

    /**
     * Create a new conversation
     */
    public function createConversation(
        User $creator,
        string $type,
        array $participantIds,
        ?string $name = null
    ): Conversation {
        $conversation = Conversation::create([
            'type'       => $type,
            'subject'    => $name,
            'created_by' => $creator->id,
            'last_message_at' => now(),
        ]);

        // Add participants
        $participants = array_unique(array_merge([$creator->id], $participantIds));
        $conversation->participants()->attach($participants);

        return $conversation;
    }

    /**
     * Mark conversation messages as read
     */
    public function markAsRead(Conversation $conversation, User $user): void
    {
        $this->messageService->markAsRead($conversation->id, $user->id);

        // Update pivot table
        $conversation->participants()
            ->where('user_id', $user->id)
            ->update(['last_read_at' => now()]);
    }

    /**
     * Get user's conversations
     */
    public function getUserConversations(User $user, string $filter = 'all'): Collection
    {
        $query = Conversation::whereHas('participants', fn($q) =>
            $q->where('user_id', $user->id)
        )
        ->with(['participants', 'lastMessage.sender'])
        ->orderByDesc('last_message_at');

        if ($filter === 'unread') {
            $query->whereHas('participants', fn($q) =>
                $q->where('user_id', $user->id)
                    ->where('unread_count', '>', 0)
            );
        } elseif ($filter === 'groups') {
            $query->whereIn('type', ['group', 'class']);
        }

        return $query->get();
    }

    /**
     * Check if user can message another user
     */
    public function canMessageUser(User $sender, User $recipient): bool
    {
        return $this->permissions->canMessage($sender, $recipient);
    }

    /**
     * Get available users for chat with permissions filtering
     */
    public function getAvailableUsersForChat(User $user): Collection
    {
        return $this->permissions->getAllowedContacts($user);
    }

    /**
     * Search available contacts
     */
    public function searchContacts(User $user, string $search): Collection
    {
        return $this->permissions->searchAllowedContacts($user, $search);
    }

    // ─── WebRTC / Appels ────────────────────────────────────────────────────

    /**
     * Initiate a call (audio or video)
     */
    public function initiateCall(User $caller, User $recipient, string $callType = 'audio', ?string $roomId = null): array
    {
        // Verify users can message each other
        if (!$this->canMessageUser($caller, $recipient)) {
            throw new \Exception('Users cannot communicate');
        }

        $peerId = uniqid('peer_', true);
        $roomId = $roomId ?? uniqid('room_', true);

        // Broadcast the call
        event(new CallInitiated(
            $caller->id,
            $caller->name,
            $recipient->id,
            $peerId,
            $callType,
            $roomId
        ));

        return [
            'peer_id' => $peerId,
            'room_id' => $roomId,
            'call_type' => $callType,
            'caller_id' => $caller->id,
            'recipient_id' => $recipient->id,
        ];
    }

    /**
     * Get user's allowed roles for chat
     */
    private function getAllowedRoles(string $role): array
    {
        return match($role) {
            'admin', 'censeur', 'intendant' =>
                ['admin', 'censeur', 'intendant', 'professeur', 'prof_principal', 'parent', 'student'],
            'professeur', 'prof_principal' =>
                ['admin', 'censeur', 'intendant', 'professeur', 'prof_principal', 'parent', 'student'],
            'parent' =>
                ['admin', 'censeur', 'intendant', 'professeur', 'prof_principal', 'parent'],
            'student' =>
                ['admin', 'censeur', 'intendant', 'professeur', 'prof_principal', 'student'],
        };
    }
}
