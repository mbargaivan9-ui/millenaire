<?php

/**
 * SettingsUpdated — Broadcast Event
 *
 * Diffusé quand l'admin met à jour les paramètres de l'établissement.
 * Écoute: window.Echo.channel('settings').listen('SettingsUpdated', ...)
 * La page publique met à jour le DOM sans rechargement.
 *
 * Phase 3 — Section 4.1
 */

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SettingsUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public array $settings) {}

    public function broadcastOn(): array
    {
        return [new Channel('settings')];
    }

    public function broadcastAs(): string
    {
        return 'SettingsUpdated';
    }

    public function broadcastWith(): array
    {
        return [
            'platform_name'  => $this->settings['platform_name'] ?? null,
            'hero_title'     => $this->settings['hero_title'] ?? null,
            'hero_subtitle'  => $this->settings['hero_subtitle'] ?? null,
            'primary_color'  => $this->settings['primary_color'] ?? null,
        ];
    }
}
