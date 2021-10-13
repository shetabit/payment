<?php

namespace Shetabit\Payment\Provider;

use Shetabit\Multipay\Payment;
use Illuminate\Support\ServiceProvider;
use Shetabit\Multipay\Request;
use Shetabit\Payment\Events\InvoicePurchasedEvent;
use Shetabit\Payment\Events\InvoiceVerifiedEvent;

class PaymentServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'shetabitPayment');

        /**
         * Configurations that needs to be done by user.
         */
        $this->publishes(
            [
                Payment::getDefaultConfigPath() => config_path('payment.php'),
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
        Request::overwrite('input', function ($key) {
            return \request($key);
        });

        /**
         * Bind to service container.
         */
        $this->app->bind('shetabit-payment', function () {
            $config = config('payment') ?? [];

            return new Payment($config);
        });

        $this->registerEvents();

        // use blade to render redirection form
        Payment::setRedirectionFormViewRenderer(function ($view, $action, $inputs, $method) {
            return view('shetabitPayment::redirectForm')->with(
                [
                    'action' => $action,
                    'inputs' => $inputs,
                    'method' => $method,
                ]
            );
        });
    }

    /**
     * Register Laravel events.
     *
     * @return void
     */
    public function registerEvents()
    {
        Payment::addPurchaseListener(function ($driver, $invoice) {
            event(new InvoicePurchasedEvent($driver, $invoice));
        });

        Payment::addVerifyListener(function ($reciept, $driver, $invoice) {
            event(new InvoiceVerifiedEvent($reciept, $driver, $invoice));
        });
    }
}
