<?php

namespace Shetabit\Payment\Facade;

use Illuminate\Support\Facades\Facade;

/**
 * Class Payment
 *
 * @package Shetabit\Payment\Facade
 *
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
