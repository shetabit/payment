<?php

namespace Shetabit\Payment\Tests;

use Illuminate\Foundation\Application;
use Orchestra\Testbench\TestCase as BaseTestCase;
use ReflectionProperty;
use Shetabit\Multipay\Invoice;
use Shetabit\Multipay\Payment as MultipayPayment;
use Shetabit\Payment\Facade\Payment;
use Shetabit\Payment\Provider\PaymentServiceProvider;
use Shetabit\Payment\Tests\Drivers\BarDriver;

abstract class TestCase extends BaseTestCase
{
    /**
     * The views the test published into the application, to be removed again.
     *
     * @var array<int, string>
     */
    private array $publishedViews = [];

    /**
     * Get the package's service providers.
     *
     * @param  Application  $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app) : array
    {
        return [PaymentServiceProvider::class];
    }

    /**
     * Get the package's facade aliases.
     *
     * @param  Application  $app
     * @return array<string, class-string>
     */
    protected function getPackageAliases($app) : array
    {
        return [
            'Payment' => Payment::class,
        ];
    }

    /**
     * Define the environment the tests run in.
     *
     * @param  Application  $app
     */
    protected function defineEnvironment($app) : void
    {
        $app['config']->set('payment', $this->paymentConfig());
    }

    protected function tearDown() : void
    {
        $this->removePublishedViews();

        parent::tearDown();
    }

    /**
     * The payment configuration of the application under test.
     *
     * It is the configuration that ships with the payment manager, with the
     * test driver added to it.
     *
     * @return array<string, mixed>
     */
    protected function paymentConfig() : array
    {
        $config = require MultipayPayment::getDefaultConfigPath();

        $config['default'] = BarDriver::DRIVER_NAME;
        $config['drivers'][BarDriver::DRIVER_NAME] = ['callbackUrl' => '/callback'];
        $config['map'][BarDriver::DRIVER_NAME] = BarDriver::class;

        return $config;
    }

    /**
     * An invoice of the given amount.
     */
    protected function invoice(int $amount = 10000) : Invoice
    {
        return new Invoice()->amount($amount);
    }

    /**
     * An instance of the test driver, with the settings of the test config.
     */
    protected function driver(Invoice|null $invoice = null) : BarDriver
    {
        return new BarDriver($invoice ?? $this->invoice(), ['callbackUrl' => '/callback']);
    }

    /**
     * Publish a view of the package the way `artisan vendor:publish` would.
     *
     * The published views are picked up while the view factory is being
     * resolved, so the application is refreshed afterwards.
     */
    protected function publishView(string $name, string $contents) : string
    {
        $directory = resource_path('views/vendor/'.PaymentServiceProvider::VIEW_NAMESPACE);

        if (! is_dir($directory)) {
            mkdir($directory, 0o777, true);
        }

        file_put_contents($view = $directory.'/'.$name.'.blade.php', $contents);

        $this->publishedViews[] = $view;

        $this->refreshApplication();
        $this->clearCompiledViews();

        return $view;
    }

    /**
     * Overwrite the value of a (protected) static property.
     *
     * @param class-string $class
     */
    protected function setStaticProperty(string $class, string $property, mixed $value) : void
    {
        new ReflectionProperty($class, $property)->setValue(null, $value);
    }

    /**
     * Throw the compiled views away.
     *
     * Blade decides whether a compiled view is still up to date by comparing
     * modification times, which have a resolution of a second. Two tests that
     * publish a different view under the same name would otherwise render
     * whatever the first of them compiled.
     */
    private function clearCompiledViews() : void
    {
        foreach (glob(config('view.compiled').'/*.php') ?: [] as $compiled) {
            unlink($compiled);
        }
    }

    /**
     * Remove the views a test published, so that it does not leak into the next.
     */
    private function removePublishedViews() : void
    {
        foreach ($this->publishedViews as $view) {
            if (is_file($view)) {
                unlink($view);
            }
        }

        $this->publishedViews = [];
    }
}
