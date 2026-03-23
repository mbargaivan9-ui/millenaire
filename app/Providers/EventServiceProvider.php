<?php
/**
 * EventServiceProvider — Fournisseur d'événements
 *
 * Enregistre les listeners pour les événements d'application.
 *
 * @package App\Providers
 */

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Events\ProfPrincipalAssigned;
use App\Listeners\InvalidateProfPrincipalSessionCache;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        ProfPrincipalAssigned::class => [
            InvalidateProfPrincipalSessionCache::class,
        ],
    ];

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}