<?php

namespace App\Providers;

use App\Services\Payment\OrangeMoneyService;
use App\Services\Payment\MtnMomoService;
use App\Services\Payment\PaymentOrchestrator;
use App\Services\Payment\ReceiptService;
use Illuminate\Support\ServiceProvider;

/**
 * SchoolPayServiceProvider
 *
 * Enregistre les services du module de paiement.
 *
 * 📌 INSTALLATION :
 *  Ajoutez dans config/app.php, tableau 'providers' :
 *     App\Providers\SchoolPayServiceProvider::class,
 *
 *  Ou dans bootstrap/providers.php (Laravel 11+) :
 *     App\Providers\SchoolPayServiceProvider::class,
 */
class SchoolPayServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Charger la config
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/schoolpay.php',
            'schoolpay'
        );

        $isSandbox = config('schoolpay.sandbox', true);

        // Lier les services
        $this->app->singleton(OrangeMoneyService::class, fn() =>
            new OrangeMoneyService($isSandbox)
        );

        $this->app->singleton(MtnMomoService::class, fn() =>
            new MtnMomoService($isSandbox)
        );

        $this->app->singleton(ReceiptService::class);

        $this->app->singleton(PaymentOrchestrator::class, fn($app) =>
            new PaymentOrchestrator(
                $app->make(OrangeMoneyService::class),
                $app->make(MtnMomoService::class),
                $app->make(ReceiptService::class),
            )
        );
    }

    public function boot(): void
    {
        // Charger les routes du module
        $this->loadRoutesFrom(__DIR__ . '/../../routes/schoolpay.php');
    }
}
