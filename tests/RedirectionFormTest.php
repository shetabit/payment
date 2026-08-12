<?php

namespace Shetabit\Payment\Tests;

use RuntimeException;
use Shetabit\Multipay\Payment as MultipayPayment;
use Shetabit\Multipay\RedirectionForm;
use Shetabit\Payment\Facade\Payment;
use Shetabit\Payment\Tests\Drivers\BarDriver;

/**
 * The package renders the redirection form of the payment manager with blade,
 * so that a CSRF field can be added to it.
 */
final class RedirectionFormTest extends TestCase
{
    public function testItRendersTheFormOfThePaymentManagerWithBlade() : void
    {
        $output = Payment::amount(12500)->purchase()->pay()->render();

        $this->assertStringContainsString('action="'.BarDriver::GATEWAY_URL.'"', $output);
        $this->assertStringContainsString('method="POST"', $output);
        $this->assertStringContainsString('name="amount"', $output);
        $this->assertStringContainsString('value="12500"', $output);
    }

    public function testItAddsACsrfFieldToTheForm() : void
    {
        $this->startSession();

        $output = Payment::amount(10000)->purchase()->pay()->render();

        $this->assertStringContainsString('name="_token"', $output);
        $this->assertStringContainsString(csrf_token(), $output);
    }

    public function testTheFormIsRenderedWhenItIsCastedToAString() : void
    {
        $this->assertStringContainsString(
            'action="'.BarDriver::GATEWAY_URL.'"',
            (string) Payment::amount(10000)->purchase()->pay()
        );
    }

    public function testItRendersTheViewOfThePackageOnceItWasPublished() : void
    {
        // The view of the package is only used once it was published, so this
        // renders the very file `vendor:publish --tag=payment-views` copies.
        // Publishing refreshes the application, so the session is started after.
        $this->publishView('redirectForm', $this->viewOfThePackage());

        $this->startSession();

        $output = Payment::amount(17500)->purchase()->pay()->render();

        $this->assertStringContainsString('action="'.BarDriver::GATEWAY_URL.'"', $output);
        $this->assertStringContainsString('method="POST"', $output);
        $this->assertStringContainsString('value="17500"', $output);
        $this->assertStringContainsString(csrf_token(), $output);
    }

    public function testItRendersTheViewTheUserPublished() : void
    {
        $this->publishView(
            'redirectForm',
            'published-view:{{ $method }}:{{ $action }}:{{ implode(",", array_keys($inputs)) }}'
        );

        $this->assertSame(
            'published-view:POST:'.BarDriver::GATEWAY_URL.':amount,callbackUrl',
            Payment::amount(10000)->purchase()->pay()->render()
        );
    }

    public function testItRendersAViewOfAnotherPathThePaymentManagerWasGiven() : void
    {
        MultipayPayment::setRedirectionFormViewPath(__DIR__.'/Fixtures/views/custom-form.php');

        $this->assertStringContainsString(
            'custom-view:POST:'.BarDriver::GATEWAY_URL.':amount,callbackUrl',
            Payment::amount(10000)->purchase()->pay()->render()
        );
    }

    public function testItFailsWhenTheViewCanNotBeRead() : void
    {
        MultipayPayment::setRedirectionFormViewPath(__DIR__.'/Fixtures/views/a-view-that-does-not-exist.php');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("The redirection form's view");

        Payment::amount(10000)->purchase()->pay()->render();
    }

    protected function tearDown() : void
    {
        // The view path is kept in a static property of the payment manager.
        $this->setStaticProperty(RedirectionForm::class, 'viewPath', null);

        parent::tearDown();
    }

    /**
     * The source of the redirection form's view that ships with the package.
     */
    private function viewOfThePackage() : string
    {
        return (string) file_get_contents(dirname(__DIR__).'/resources/views/redirectForm.blade.php');
    }
}
