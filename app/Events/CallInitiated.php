<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CallInitiated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $callerId;
    public $callerName;
    public $recipientId;
    public $peerId;
    public $callType; // 'audio' or 'video'
    public $roomId;

    public function __construct(int $callerId, string $callerName, int $recipientId, string $peerId, string $callType, ?string $roomId = null)
    {
        $this->callerId = $callerId;
        $this->callerName = $callerName;
        $this->recipientId = $recipientId;
        $this->peerId = $peerId;
        $this->callType = $callType;
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
            'caller_id' => $this->callerId,
            'caller_name' => $this->callerName,
            'recipient_id' => $this->recipientId,
            'peer_id' => $this->peerId,
            'call_type' => $this->callType,
            'room_id' => $this->roomId,
            'timestamp' => now(),
        ];
    }
}
