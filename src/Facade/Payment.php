<?php

namespace Shetabit\Payment\Facade;

use Illuminate\Support\Facades\Facade;
use Shetabit\Multipay\Invoice;

/**
 * Class Payment
 *
 * @method static config($key, $value = null)
 * @method static callbackUrl($url = null)
 * @method static resetCallbackUrl()
 * @method static amount($amount)
 * @method static detail($key, $value = null)
 * @method static transactionId($id)
 * @method static via($driver)
 * @method static purchase(Invoice $invoice = null, $finalizeCallback = null)
 * @method static pay($initializeCallback = null)
 * @method static verify($finalizeCallback = null)
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
