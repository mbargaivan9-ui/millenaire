<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\Channel as BroadcastChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserOnlineStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userId;
    public $userName;
    public $isOnline;
    public $lastLogin;

    public function __construct(int $userId, string $userName, bool $isOnline, $lastLogin = null)
    {
        $this->userId = $userId;
        $this->userName = $userName;
        $this->isOnline = $isOnline;
        $this->lastLogin = $lastLogin;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new BroadcastChannel('presence'),
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'user_id' => $this->userId,
            'user_name' => $this->userName,
            'is_online' => $this->isOnline,
            'last_login' => $this->lastLogin,
            'timestamp' => now(),
        ];
    }
}
