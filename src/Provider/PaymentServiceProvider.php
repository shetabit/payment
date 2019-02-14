<?php

namespace Shetabit\Payment\Provider;

use Shetabit\Payment\PaymentManager;
use Illuminate\Support\ServiceProvider;

class PaymentServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        /**
         * Configurations that needs to be done by user.
         */
        $this->publishes([
            __DIR__.'/../Config/payment.php' => config_path('payment.php'),
        ], 'config');

        /**
         * Bind to service container.
         */
        $this->app->bind('shetabit-payment', function() {
            return new PaymentManager(config('payment'));
        });
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
