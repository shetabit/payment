<?php

namespace Shetabit\Payment\Tests;

use Shetabit\Multipay\Contracts\ReceiptInterface;
use Shetabit\Multipay\Payment as MultipayPayment;
use Shetabit\Multipay\RedirectionForm;
use Shetabit\Payment\Facade\Payment;
use Shetabit\Payment\Tests\Drivers\BarDriver;

/**
 * The payment manager, driven through the facade of the package.
 */
final class PaymentTest extends TestCase
{
    public function testItPurchasesAnInvoice() : void
    {
        $invoice = $this->invoice(25000);

        $payment = Payment::purchase($invoice);

        $this->assertInstanceOf(MultipayPayment::class, $payment);
        $this->assertSame(BarDriver::TRANSACTION_ID, $invoice->getTransactionId());
    }

    public function testItHandsThePurchasedInvoiceToTheFinalizeCallback() : void
    {
        $receivedDriver = null;
        $receivedTransactionId = null;

        Payment::amount(10000)->purchase(
            null,
            function (BarDriver $driver, string $transactionId) use (&$receivedDriver, &$receivedTransactionId): void {
                $receivedDriver = $driver;
                $receivedTransactionId = $transactionId;
            }
        );

        $this->assertSame(BarDriver::TRANSACTION_ID, $receivedTransactionId);
        $this->assertSame(10000, $receivedDriver?->getInvoice()->getAmount());
    }

    public function testItPaysAPurchasedInvoice() : void
    {
        $form = Payment::amount(15000)->purchase()->pay();

        $this->assertInstanceOf(RedirectionForm::class, $form);
        $this->assertSame(BarDriver::GATEWAY_URL, $form->getAction());
        $this->assertSame('POST', $form->getMethod());
        $this->assertSame(15000, $form->getInputs()['amount']);
    }

    public function testItPaysWithoutPurchasingFirst() : void
    {
        // The manager creates the driver on the fly when `pay()` is called
        // without a purchase before it.
        $this->assertInstanceOf(RedirectionForm::class, Payment::amount(15000)->pay());
    }

    public function testItVerifiesAPayment() : void
    {
        $receipt = Payment::amount(10000)->purchase()->verify();

        $this->assertInstanceOf(ReceiptInterface::class, $receipt);
        $this->assertSame(BarDriver::DRIVER_NAME, $receipt->getDriver());
        $this->assertSame(BarDriver::REFERENCE_ID, $receipt->getReferenceId());
    }

    public function testItHandsTheReceiptToTheFinalizeCallback() : void
    {
        $received = null;

        Payment::amount(10000)->purchase()->verify(function (ReceiptInterface $receipt) use (&$received): void {
            $received = $receipt;
        });

        $this->assertSame(BarDriver::REFERENCE_ID, $received?->getReferenceId());
    }

    public function testTheDriverCanBeChangedOnTheFly() : void
    {
        $this->assertSame(
            BarDriver::DRIVER_NAME,
            Payment::via(BarDriver::DRIVER_NAME)->amount(10000)->purchase()->verify()->getDriver()
        );
    }

    public function testTheCallbackUrlCanBeChangedOnTheFly() : void
    {
        $inputs = Payment::callbackUrl('https://example.com/callback')->amount(10000)->purchase()->pay()->getInputs();

        $this->assertSame('https://example.com/callback', $inputs['callbackUrl']);
    }

    public function testTheCallbackUrlCanBeResetToTheConfiguredOne() : void
    {
        $inputs = Payment::callbackUrl('https://example.com/callback')
            ->resetCallbackUrl()
            ->amount(10000)
            ->purchase()
            ->pay()
            ->getInputs();

        // Restoring the configured callback url is what `shetabit/multipay`
        // 3.0.1 fixed, which is why the package requires it.
        $this->assertSame('/callback', $inputs['callbackUrl']);
    }

    public function testASettingCanBeOverwrittenOnTheFly() : void
    {
        $inputs = Payment::config('callbackUrl', '/from-the-config-method')
            ->amount(10000)
            ->purchase()
            ->pay()
            ->getInputs();

        $this->assertSame('/from-the-config-method', $inputs['callbackUrl']);
    }

    public function testTheDetailsOfTheInvoiceCanBeSet() : void
    {
        $driver = null;

        Payment::detail('order_id', 42)
            ->amount(10000)
            ->purchase(null, function (BarDriver $bar) use (&$driver): void {
                $driver = $bar;
            });

        $this->assertSame(42, $driver?->getInvoice()->getDetails()['order_id']);
    }

    public function testTheTransactionIdOfTheInvoiceCanBeSet() : void
    {
        $invoice = $this->invoice();

        Payment::transactionId('a-transaction-of-the-gateway')->purchase($invoice);

        // The invoice keeps the id until the driver replaces it with the one
        // the gateway handed out.
        $this->assertSame(BarDriver::TRANSACTION_ID, $invoice->getTransactionId());
    }
}
