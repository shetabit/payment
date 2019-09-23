<?php

namespace Shetabit\Payment\Abstracts;

use Shetabit\Payment\Contracts\ReceiptInterface;

abstract class Receipt implements ReceiptInterface
{
    protected $trackingCode;

    public function getTrackingCode()
    {
        return $this->trackingCode;
    }
}
