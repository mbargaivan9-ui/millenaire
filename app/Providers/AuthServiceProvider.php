<?php
/**
 * AuthServiceProvider — Fournisseur d'authentification et autorisation
 *
 * Configure les policies (autorisations) d'autorisation pour les modèles.
 *
 * @package App\Providers
 */

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Boot the authentication services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Register policies
        // Format: Model::class => Policy::class
    }

    /**
     * Register policies for models.
     *
     * @return void
     */
    protected function registerPolicies(): void
    {
        // No policies registered
    }
}