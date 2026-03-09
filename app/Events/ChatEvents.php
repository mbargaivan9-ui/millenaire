<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\{PrivateChannel, InteractsWithSockets};
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

// ─────────────────────────────────────────────────────────────────────────────

/**
 * MessageSent — Broadcast quand un nouveau message est envoyé.
 * Channel: private "conversation.{conversationId}"
 */
class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Message $message) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel("conversation.{$this->message->conversation_id}")];
    }

    public function broadcastAs(): string { return 'MessageSent'; }

    public function broadcastWith(): array
    {
        return [
            'id'              => $this->message->id,
            'conversation_id' => $this->message->conversation_id,
            'sender_id'       => $this->message->sender_id,
            'sender_name'     => $this->message->sender?->name,
            'sender_avatar'   => $this->message->sender?->avatar_url,
            'content'         => $this->message->is_deleted_for_all
                                    ? null
                                    : $this->message->content,
            'is_edited'       => $this->message->is_edited,
            'created_at'      => $this->message->created_at?->toIso8601String(),
        ];
    }
}

// ─────────────────────────────────────────────────────────────────────────────

/**
 * MessageRead — Double tick vert — message lu par le destinataire.
 * Channel: private "conversation.{conversationId}"
 */
class MessageRead implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $conversationId,
        public int $messageId,
        public int $readByUserId,
        public string $readAt
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel("conversation.{$this->conversationId}")];
    }

    public function broadcastAs(): string { return 'MessageRead'; }

    public function broadcastWith(): array
    {
        return [
            'message_id'    => $this->messageId,
            'read_by'       => $this->readByUserId,
            'read_at'       => $this->readAt,
        ];
    }
}

// ─────────────────────────────────────────────────────────────────────────────

/**
 * UserTyping — Indicateur "... est en train d'écrire"
 * Channel: private "conversation.{conversationId}"
 */
class UserTyping implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int    $conversationId,
        public int    $userId,
        public string $userName,
        public bool   $isTyping
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel("conversation.{$this->conversationId}")];
    }

    public function broadcastAs(): string { return 'UserTyping'; }

    public function broadcastWith(): array
    {
        return [
            'user_id'   => $this->userId,
            'user_name' => $this->userName,
            'is_typing' => $this->isTyping,
        ];
    }
}
