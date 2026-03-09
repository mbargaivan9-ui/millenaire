<?php

/**
 * AnnouncementPublished — Broadcast Event
 *
 * Diffusé quand une annonce est publiée.
 * La page d'accueil publique ajoute dynamiquement la carte.
 *
 * Phase 2 — Section 3.2
 */

namespace App\Events;

use App\Models\Announcement;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AnnouncementPublished implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Announcement $announcement) {}

    public function broadcastOn(): array
    {
        return [new Channel('announcements')];
    }

    public function broadcastAs(): string
    {
        return 'AnnouncementPublished';
    }

    public function broadcastWith(): array
    {
        return [
            'id'          => $this->announcement->id,
            'title'       => $this->announcement->title,
            'slug'        => $this->announcement->slug,
            'excerpt'     => $this->announcement->excerpt,
            'category'    => $this->announcement->category,
            'image_url'   => $this->announcement->image_url,
            'published_at' => $this->announcement->published_at?->toIso8601String(),
        ];
    }
}
