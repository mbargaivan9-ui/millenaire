<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\AdminService;

class AdminServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register AdminService as singleton
        $this->app->singleton(AdminService::class, function ($app) {
            return new AdminService();
        });

        // Alias for easier access
        $this->app->alias(AdminService::class, 'admin-service');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish migrations
        $this->publishes([
            __DIR__ . '/../../database/seeders' => database_path('seeders'),
        ], 'seeders');

        // Load routes if exists
        if (file_exists(base_path('routes/api_admin.php'))) {
            require base_path('routes/api_admin.php');
        }

        // Share admin service with views
        view()->share('adminService', $this->app->make(AdminService::class));
    }
}
