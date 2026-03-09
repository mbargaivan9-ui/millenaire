<?php namespace App\Events;
use Illuminate\Broadcasting\{Channel,PrivateChannel,InteractsWithSockets};
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * StudentAbsenceRecorded — Broadcast à private channel "guardian.{userId}"
 * Notifie les parents en temps réel quand une absence est enregistrée.
 */
class StudentAbsenceRecorded implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public array  $data,
        public int    $guardianUserId
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel("guardian.{$this->guardianUserId}")];
    }

    public function broadcastAs(): string { return 'StudentAbsenceRecorded'; }
    public function broadcastWith(): array { return $this->data; }
}
