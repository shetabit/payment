<?php

namespace Shetabit\Payment\Tests;

use Shetabit\Payment\Provider\PaymentServiceProvider;
use Shetabit\Payment\Tests\Drivers\BarDriver;

/**
 * An application's `config/payment.php` is loaded before the providers are
 * registered, which is when the package merges the configuration that ships
 * with the payment manager into it. The tests below register the provider
 * again to reach that moment.
 */
final class ConfigurationTest extends TestCase
{
    public function testTheConfigurationOfThePackageIsMergedIntoTheApplications() : void
    {
        $this->registerProviderWith(['default' => BarDriver::DRIVER_NAME]);

        // Neither the drivers nor their map were set by the application, both
        // come from the configuration file that ships with the payment manager.
        $this->assertSame(BarDriver::DRIVER_NAME, config('payment.default'));
        $this->assertArrayHasKey('zarinpal', config('payment.drivers'));
        $this->assertArrayHasKey('zarinpal', config('payment.map'));
    }

    public function testTheConfigurationOfTheApplicationWins() : void
    {
        $this->registerProviderWith([
            'default' => BarDriver::DRIVER_NAME,
            'map' => ['zarinpal' => BarDriver::class],
        ]);

        $this->assertSame(BarDriver::class, config('payment.map.zarinpal'));
    }

    public function testTheConfigurationIsMergedOneLevelDeep() : void
    {
        // Anything the application configures replaces the whole section of the
        // package, so a driver that is missing from the application's `map` stays
        // missing. That is what `artisan vendor:publish --tag=payment-config`
        // is for: it hands the full list over to the application.
        $this->registerProviderWith([
            'default' => BarDriver::DRIVER_NAME,
            'map' => [BarDriver::DRIVER_NAME => BarDriver::class],
        ]);

        $this->assertSame([BarDriver::DRIVER_NAME => BarDriver::class], config('payment.map'));
    }

    /**
     * Register the provider on top of the given payment configuration.
     *
     * @param array<string, mixed> $config
     */
    private function registerProviderWith(array $config) : void
    {
        config()->set('payment', $config);

        $this->app->register(new PaymentServiceProvider($this->app), true);
    }
}
