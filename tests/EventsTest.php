<?php

namespace Shetabit\Payment\Tests;

use Error;
use Illuminate\Support\Facades\Event;
use Shetabit\Multipay\Contracts\ReceiptInterface;
use Shetabit\Payment\Events\InvoicePurchasedEvent;
use Shetabit\Payment\Events\InvoiceVerifiedEvent;
use Shetabit\Payment\Facade\Payment;
use Shetabit\Payment\Provider\PaymentServiceProvider;
use Shetabit\Payment\Tests\Drivers\BarDriver;

final class EventsTest extends TestCase
{
    public function testItDispatchesAnEventWhenAnInvoiceWasPurchased() : void
    {
        Event::fake([InvoicePurchasedEvent::class]);

        $invoice = $this->invoice(30000);

        Payment::purchase($invoice);

        Event::assertDispatched(
            InvoicePurchasedEvent::class,
            fn (InvoicePurchasedEvent $event): bool => $event->driver instanceof BarDriver
                && $event->invoice === $invoice
        );
    }

    public function testItDispatchesAnEventWhenAPaymentWasVerified() : void
    {
        Event::fake([InvoiceVerifiedEvent::class]);

        $invoice = $this->invoice(30000);

        Payment::purchase($invoice)->verify();

        Event::assertDispatched(
            InvoiceVerifiedEvent::class,
            fn (InvoiceVerifiedEvent $event): bool => $event->receipt->getReferenceId() === BarDriver::REFERENCE_ID
                && $event->driver instanceof BarDriver
                && $event->invoice === $invoice
        );
    }

    public function testAPurchaseDoesNotDispatchTheVerificationEvent() : void
    {
        Event::fake([InvoicePurchasedEvent::class, InvoiceVerifiedEvent::class]);

        Payment::amount(10000)->purchase();

        Event::assertDispatched(InvoicePurchasedEvent::class);
        Event::assertNotDispatched(InvoiceVerifiedEvent::class);
    }

    public function testItRegistersItsListenersOnlyOnce() : void
    {
        // The payment manager keeps its listeners in a static property, so they
        // outlive the application they were registered from. Registering the
        // provider again must not dispatch the events twice.
        $this->app->register(new PaymentServiceProvider($this->app), true);

        Event::fake([InvoicePurchasedEvent::class]);

        Payment::amount(10000)->purchase();

        Event::assertDispatchedTimes(InvoicePurchasedEvent::class, 1);
    }

    public function testThePurchaseEventKeepsWhatItWasGiven() : void
    {
        $invoice = $this->invoice();
        $driver = $this->driver($invoice);

        $event = new InvoicePurchasedEvent($driver, $invoice);

        $this->assertSame($driver, $event->driver);
        $this->assertSame($invoice, $event->invoice);
    }

    public function testTheVerificationEventKeepsWhatItWasGiven() : void
    {
        $invoice = $this->invoice();
        $driver = $this->driver($invoice);
        $receipt = $driver->verify();

        $event = new InvoiceVerifiedEvent($receipt, $driver, $invoice);

        $this->assertInstanceOf(ReceiptInterface::class, $event->receipt);
        $this->assertSame($receipt, $event->receipt);
        $this->assertSame($driver, $event->driver);
        $this->assertSame($invoice, $event->invoice);
    }

    public function testTheEventsCanNotBeChangedAfterTheyWereCreated() : void
    {
        $event = new InvoicePurchasedEvent($this->driver(), $this->invoice());

        $this->expectException(Error::class);
        $this->expectExceptionMessage('Cannot modify readonly property');

        // @phpstan-ignore assign.propertyProtectedSet (that is what is asserted)
        $event->invoice = $this->invoice(1);
    }
}
