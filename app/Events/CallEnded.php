<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CallEnded implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $userId;
    public $recipientId;
    public $roomId;
    public $reason; // 'rejected', 'ended', 'timeout'

    public function __construct(int $userId, int $recipientId, string $reason = 'ended', ?string $roomId = null)
    {
        $this->userId = $userId;
        $this->recipientId = $recipientId;
        $this->reason = $reason;
        $this->roomId = $roomId;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->recipientId),
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'user_id' => $this->userId,
            'recipient_id' => $this->recipientId,
            'reason' => $this->reason,
            'room_id' => $this->roomId,
            'timestamp' => now(),
        ];
    }
}
