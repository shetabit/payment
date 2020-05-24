<?php

namespace Shetabit\Payment\Contracts;

use Carbon\Carbon;
use Shetabit\Payment\Exceptions\NoTransactionIdException;

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
     * Retrieve transaction's ID.
     *
     * @return string
     * @throws NoTransactionIdException
     */
    public function getTransactionId() : string;

    /**
     * Retrieve payment date
     *
     * @return Carbon|\Illuminate\Support\Carbon
     */
    public function getDate() : Carbon;
}
