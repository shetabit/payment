<?php

namespace Shetabit\Payment\Contracts;

use Carbon\Carbon;

interface ReceiptInterface
{
    /**
     * Retrieve driver's name
     *
     * @return string
     */
    public function getDriver() : string;

    /**
     * Retrieve payment reference code.
     *
     * @return string
     */
    public function getReferenceId() : string;

    /**
     * Retrieve payment date
     *
     * @return Carbon|\Illuminate\Support\Carbon
     */
    public function getDate() : Carbon;
}
