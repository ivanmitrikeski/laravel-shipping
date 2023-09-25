<?php

namespace Mitrik\Shipping;

use Illuminate\Support\ServiceProvider;
use Mitrik\Shipping\Facades\Shipping;

class ShippingServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->registerPublishables();

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        $this->registerFacades();
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {

    }

    /**
     * @return void
     */
    protected function registerFacades(): void
    {
        $this->app->singleton(Shipping::class, function ($app) {
            return new Shipping();
        });
    }

    /**
     * @return void
     */
    protected function registerPublishables(): void
    {
        $this->publishes([
            __DIR__ . '/../config/shipping.php' => config_path('shipping.php')
        ], 'mitrik-shipping-config');
    }

}
