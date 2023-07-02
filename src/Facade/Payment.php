<?php

namespace Shetabit\Payment\Facade;

use Illuminate\Support\Facades\Facade;
use Shetabit\Multipay\Invoice;
use Shetabit\Multipay\Contracts\ReceiptInterface;
use Shetabit\Multipay\Payment as MultipayPayment;

/**
 * Class Payment
 *
 * @method static MultipayPayment config($key, $value = null)
 * @method static MultipayPayment callbackUrl($url = null)
 * @method static MultipayPayment resetCallbackUrl()
 * @method static MultipayPayment amount($amount)
 * @method static MultipayPayment detail($key, $value = null)
 * @method static MultipayPayment transactionId($id)
 * @method static MultipayPayment via($driver)
 * @method static MultipayPayment purchase(Invoice $invoice = null, $finalizeCallback = null)
 * @method static mixed pay($initializeCallback = null)
 * @method static ReceiptInterface verify($finalizeCallback = null)
 *
 * @package Shetabit\Payment\Facade
 * @see \Shetabit\Multipay\Payment
 */
class Payment extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    public static function getFacadeAccessor()
    {
        return 'shetabit-payment';
    }
}
