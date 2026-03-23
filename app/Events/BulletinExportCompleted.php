<?php

namespace App\Events;

use App\Models\BulletinTemplate;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithBroadcasting;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BulletinExportCompleted
{
    use Dispatchable, InteractsWithBroadcasting, SerializesModels;

    public BulletinTemplate $template;
    public string $filename;
    public ?int $userId;

    /**
     * Create a new event instance.
     */
    public function __construct(BulletinTemplate $template, string $filename, ?int $userId = null)
    {
        $this->template = $template;
        $this->filename = $filename;
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
            'filename' => $this->filename,
            'completed_at' => now(),
        ];
    }
}
