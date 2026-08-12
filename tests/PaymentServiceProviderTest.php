<?php

namespace Shetabit\Payment\Tests;

use Illuminate\Http\Request as LaravelRequest;
use Illuminate\Support\ServiceProvider;
use Shetabit\Multipay\Payment as MultipayPayment;
use Shetabit\Multipay\Request as MultipayRequest;
use Shetabit\Payment\Facade\Payment;
use Shetabit\Payment\Provider\PaymentServiceProvider;

final class PaymentServiceProviderTest extends TestCase
{
    public function testItBindsThePaymentManagerToTheContainer() : void
    {
        $this->assertInstanceOf(MultipayPayment::class, $this->app->make(Payment::SERVICE_NAME));
    }

    public function testThePaymentManagerCanBeResolvedByItsClassName() : void
    {
        $this->assertInstanceOf(MultipayPayment::class, $this->app->make(MultipayPayment::class));
    }

    public function testEveryResolveReturnsItsOwnPaymentManager() : void
    {
        // The manager holds on to the invoice that is being paid, so two
        // callers must not end up sharing a single instance.
        $this->assertNotSame(
            $this->app->make(Payment::SERVICE_NAME),
            $this->app->make(Payment::SERVICE_NAME)
        );
    }

    public function testTheFacadeResolvesThePaymentManager() : void
    {
        $this->assertSame(Payment::SERVICE_NAME, Payment::getFacadeAccessor());
        $this->assertInstanceOf(MultipayPayment::class, Payment::getFacadeRoot());
    }

    public function testThePaymentManagerIsBuiltWithTheApplicationsConfiguration() : void
    {
        $inputs = Payment::amount(10000)->purchase()->pay()->getInputs();

        // The test driver hands the settings it was given back through the
        // inputs of its redirection form.
        $this->assertSame('/callback', $inputs['callbackUrl']);
    }

    public function testItRegistersTheViewsOfThePackage() : void
    {
        $this->assertTrue(view()->exists(PaymentServiceProvider::VIEW_NAMESPACE.'::redirectForm'));
    }

    public function testItPublishesTheConfigurationFile() : void
    {
        $this->assertSame(
            [MultipayPayment::getDefaultConfigPath() => config_path('payment.php')],
            ServiceProvider::pathsToPublish(PaymentServiceProvider::class, 'payment-config')
        );
    }

    public function testItPublishesTheViewsOfThePackage() : void
    {
        $this->assertSame(
            [dirname(__DIR__).'/resources/views' => resource_path('views/vendor/shetabitPayment')],
            ServiceProvider::pathsToPublish(PaymentServiceProvider::class, 'payment-views')
        );
    }

    public function testItReadsTheGatewaysCallbackDataFromLaravelsRequest() : void
    {
        $this->app->instance(
            'request',
            LaravelRequest::create('/callback', 'POST', ['Authority' => 'A0000000000000000000000000000123'])
        );

        $this->assertSame('A0000000000000000000000000000123', MultipayRequest::input('Authority'));
        $this->assertNull(MultipayRequest::input('a-key-the-gateway-did-not-send'));
    }
}
