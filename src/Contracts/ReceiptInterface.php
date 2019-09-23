<?php

namespace Shetabit\Payment\Contracts;

interface ReceiptInterface
{
    /**
     * Retrieve tracking code.
     *
     * @return string|integer
     */
    public function getTrackingCode();
}
