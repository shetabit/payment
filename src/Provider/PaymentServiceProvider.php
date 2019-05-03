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
        $this->loadViewsFrom(__DIR__.'/resources/views', 'shetabitPayment');

        /**
         * Configurations that needs to be done by user.
         */
        $this->publishes(
            [
                __DIR__.'/../../config/payment.php' => config_path('payment.php'),
            ],
            'config'
        );

        /**
         * Views that needs to be modified by user.
         */
        $this->publishes(
            [
                __DIR__.'/../../resources/views' => resource_path('views/vendor/shetabitPayment')
            ],
            'views'
        );
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register()
    {
        /**
         * Bind to service container.
         */
        $this->app->bind('shetabit-payment', function () {
            return new PaymentManager(config('payment'));
        });
    }
}
