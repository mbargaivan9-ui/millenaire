<?php

namespace App\Events\Bulletin;

use App\Models\SmartBulletinTemplate;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * AiAnalysisCompleted
 *
 * Broadcasté via Laravel Reverb (WebSocket) quand l'analyse IA est terminée.
 * Permet au frontend de rediriger automatiquement vers l'éditeur template.
 *
 * Channel privé : private-template.{teacher_id}
 */
class AiAnalysisCompleted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly SmartBulletinTemplate $template,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel("template.{$this->template->created_by}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'ai.completed';
    }

    public function broadcastWith(): array
    {
        return [
            'template_id'  => $this->template->id,
            'ai_status'    => $this->template->ai_status,
            'is_ready'     => $this->template->is_ai_ready,
            'edit_url'     => route('teacher.bulletin.template.edit', $this->template->id),
        ];
    }
}
