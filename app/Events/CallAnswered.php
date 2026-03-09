<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CallAnswered implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $callerId;
    public $answererId;
    public $answererName;
    public $peerId;
    public $roomId;

    public function __construct(int $callerId, int $answererId, string $answererName, string $peerId, ?string $roomId = null)
    {
        $this->callerId = $callerId;
        $this->answererId = $answererId;
        $this->answererName = $answererName;
        $this->peerId = $peerId;
        $this->roomId = $roomId;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('user.' . $this->callerId),
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'caller_id' => $this->callerId,
            'answerer_id' => $this->answererId,
            'answerer_name' => $this->answererName,
            'peer_id' => $this->peerId,
            'room_id' => $this->roomId,
            'timestamp' => now(),
        ];
    }
}
