<?php

namespace App\Events;

use App\Models\BulletinTemplate;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithBroadcasting;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BulletinExportFailed
{
    use Dispatchable, InteractsWithBroadcasting, SerializesModels;

    public BulletinTemplate $template;
    public string $error;
    public ?int $userId;

    /**
     * Create a new event instance.
     */
    public function __construct(BulletinTemplate $template, string $error, ?int $userId = null)
    {
        $this->template = $template;
        $this->error = $error;
        $this->userId = $userId;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("bulletin-export.{$this->userId}"),
        ];
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'template_id' => $this->template->id,
            'error' => $this->error,
            'failed_at' => now(),
        ];
    }
}
