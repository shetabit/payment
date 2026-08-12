<?php

namespace Shetabit\Payment\Facade;

use Illuminate\Support\Facades\Facade;
use Shetabit\Multipay\Contracts\ReceiptInterface;
use Shetabit\Multipay\Invoice;
use Shetabit\Multipay\Payment as MultipayPayment;
use Shetabit\Multipay\RedirectionForm;

/**
 * The payment manager of the `shetabit/multipay` package, as a Laravel facade.
 *
 * @method static MultipayPayment config(array<string, mixed>|string $key, mixed $value = null)
 * @method static MultipayPayment callbackUrl(string|null $url = null)
 * @method static MultipayPayment resetCallbackUrl()
 * @method static MultipayPayment amount(mixed $amount)
 * @method static MultipayPayment detail(array<string, mixed>|string $key, mixed $value = null)
 * @method static MultipayPayment transactionId(string|int|null $id)
 * @method static MultipayPayment via(string $driver)
 * @method static MultipayPayment purchase(Invoice|null $invoice = null, callable|null $finalizeCallback = null)
 * @method static RedirectionForm pay(callable|null $initializeCallback = null)
 * @method static ReceiptInterface verify(callable|null $finalizeCallback = null)
 *
 * @see MultipayPayment
 */
class Payment extends Facade
{
    /**
     * The name the payment manager is bound to in the service container.
     */
    public const string SERVICE_NAME = 'shetabit-payment';

    /**
     * Get the registered name of the component.
     */
    public static function getFacadeAccessor() : string
    {
        return self::SERVICE_NAME;
    }
}
