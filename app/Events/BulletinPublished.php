<?php

namespace App\Events;

use Illuminate\Broadcasting\{Channel, PrivateChannel, InteractsWithSockets};
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

// ─────────────────────────────────────────────────────────────────────────────

/**
 * BulletinPublished — Notifie les parents/élèves qu'un bulletin est disponible.
 * Channel: private "guardian.{userId}"
 */
class BulletinPublished implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public array $data,
        public int   $recipientUserId
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel("guardian.{$this->recipientUserId}")];
    }

    public function broadcastAs(): string { return 'BulletinPublished'; }
    public function broadcastWith(): array { return $this->data; }
}
