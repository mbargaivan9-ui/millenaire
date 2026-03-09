<?php

/**
 * AppServiceProvider — Fournisseur de services principal
 *
 * Configuration: Locale, Pagination, Broadcasting, URL, Vite
 *
 * @package App\Providers
 */

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use App\Models\EstablishmentSetting;
use App\Contracts\RedirectServiceInterface;
use App\Contracts\AuthenticationServiceInterface;
use App\Services\RedirectService;
use App\Services\AuthenticationService;
use App\Repositories\Interfaces\PaymentRepositoryInterface;
use App\Repositories\Eloquent\EloquentPaymentRepository;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Singleton for establishment settings
        $this->app->singleton(EstablishmentSetting::class, function () {
            return EstablishmentSetting::getInstance();
        });

        // Bind service interfaces to their implementations
        $this->app->bind(RedirectServiceInterface::class, RedirectService::class);
        $this->app->bind(AuthenticationServiceInterface::class, AuthenticationService::class);
        
        // Bind repository interfaces to their implementations
        $this->app->bind(PaymentRepositoryInterface::class, EloquentPaymentRepository::class);
    }

    public function boot(): void
    {
        // Bootstrap 5 pagination
        Paginator::useBootstrapFive();

        // Force HTTPS in production
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        // Global Blade directives
        try {
            Blade::directive('money', function ($expression) {
                return "<?php echo 'XAF ' . number_format($expression, 0, ',', ' '); ?>";
            });

            Blade::directive('gradeColor', function ($expression) {
                return "<?php echo $expression < 10 ? '#ef4444' : ($expression < 13 ? '#f59e0b' : ($expression < 16 ? '#3b82f6' : '#10b981')); ?>";
            });
        } catch (\Exception $e) {
            // Silently fail if Blade directives fail
        }

        // Share settings with all views
        view()->composer('*', function ($view) {
            try {
                $settings = cache()->remember('establishment.settings', 600, 
                    fn() => EstablishmentSetting::getInstance()
                );
                $view->with('globalSettings', $settings);
            } catch (\Exception $e) {
                // If cache fails, just use fresh instance
                try {
                    $view->with('globalSettings', EstablishmentSetting::getInstance());
                } catch (\Exception $e) {
                    // If that also fails, use empty collection
                    $view->with('globalSettings', collect());
                }
            }
        });
    }
}
