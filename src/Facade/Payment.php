<?php

namespace Shetabit\Payment\Facade;

use Illuminate\Support\Facades\Facade;

/**
 * Class Sms
 *
 * @package Shetabit\Payment\Facade
 * @see \Shetabit\Payment\PaymentManager
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
