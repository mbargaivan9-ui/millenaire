<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $message;

    public function __construct(Message $message)
    {
        $this->message = $message->load(['sender', 'attachments', 'reactions.user']);
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('conversation.' . $this->message->conversation_id),
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->message->id,
            'conversation_id' => $this->message->conversation_id,
            'sender_id' => $this->message->sender_id,
            'content' => $this->message->content,
            'type' => $this->message->type,
            'sender' => [
                'id' => $this->message->sender->id,
                'name' => $this->message->sender->name,
                'profile_photo' => $this->message->sender->profile_photo,
            ],
            'attachments' => $this->message->attachments->map(function ($attachment) {
                return [
                    'id' => $attachment->id,
                    'file_name' => $attachment->file_name,
                    'file_path' => $attachment->file_path,
                    'file_type' => $attachment->file_type,
                    'url' => $attachment->getUrlAttribute(),
                ];
            }),
            'reactions' => $this->message->reactions->groupBy('emoji')->map(function ($reactions) {
                return [
                    'emoji' => $reactions->first()->emoji,
                    'count' => $reactions->count(),
                    'users' => $reactions->map(fn($r) => ['id' => $r->user_id, 'name' => $r->user->name]),
                ];
            }),
            'created_at' => $this->message->created_at->toIso8601String(),
        ];
    }
}
