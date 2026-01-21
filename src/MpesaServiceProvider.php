<?php

namespace TFS\Mpesa;

use Illuminate\Support\ServiceProvider;
use TFS\Mpesa\Mpesa;
use TFS\Mpesa\MpesaClient;

class MpesaServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot(): void
    {
        // Publish configuration file
        $this->publishes([
            __DIR__ . '/config/mpesa.php' => config_path('mpesa.php'),
        ], 'mpesa-config');

        // Publish public key certificate
        $this->publishes([
            __DIR__ . '/assets/public_keycert.cer' => storage_path('app/mpesa/public_keycert.cer'),
        ], 'mpesa-cert');

        // Register commands if running in console
        if ($this->app->runningInConsole()) {
            $this->commands([
                // Add console commands here in future versions
            ]);
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register(): void
    {
        // Merge package config with app config
        $this->mergeConfigFrom(
            __DIR__ . '/config/mpesa.php',
            'mpesa'
        );

        // Register the main Mpesa class as a singleton
        $this->app->singleton('mpesa', function ($app) {
            return new Mpesa();
        });

        // Register the MpesaClient as a singleton
        $this->app->singleton(MpesaClient::class, function ($app) {
            return new MpesaClient();
        });

        // Register services
        $this->registerServices();
    }

    /**
     * Register package services.
     *
     * @return void
     */
    protected function registerServices(): void
    {
        $services = [
            'TFS\Mpesa\Services\B2CService',
            'TFS\Mpesa\Services\B2BService',
            'TFS\Mpesa\Services\STKPushService',
            'TFS\Mpesa\Services\BalanceService',
            'TFS\Mpesa\Services\C2BService',
        ];

        foreach ($services as $service) {
            $this->app->bind($service, function ($app) use ($service) {
                return new $service($app->make(MpesaClient::class));
            });
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides(): array
    {
        return [
            'mpesa',
            MpesaClient::class,
            'TFS\Mpesa\Services\B2CService',
            'TFS\Mpesa\Services\B2BService',
            'TFS\Mpesa\Services\STKPushService',
            'TFS\Mpesa\Services\BalanceService',
            'TFS\Mpesa\Services\C2BService',
        ];
    }
}