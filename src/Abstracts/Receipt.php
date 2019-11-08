<?php

namespace Shetabit\Payment\Abstracts;

use Carbon\Carbon;
use Shetabit\Payment\Contracts\ReceiptInterface;

abstract class Receipt implements ReceiptInterface
{
    /**
     * A unique ID which is given to the customer whenever the payment is done successfully.
     * This ID can be used for financial follow up.
     *
     * @var string
     */
    protected $referenceId;

    /**
     * payment driver's name.
     *
     * @var string
     */
    protected $driver;

    /**
     * payment date
     *
     * @var Carbon
     */
    protected $date;

    /**
     * Receipt constructor.
     *
     * @param $driver
     * @param $referenceId
     */
    public function __construct($driver, $referenceId)
    {
        $this->driver = $driver;
        $this->referenceId = $referenceId;
        $this->date = now();
    }

    /**
     * Retrieve driver's name
     *
     * @return string
     */
    public function getDriver() : string
    {
        return $this->driver;
    }

    /**
     * Retrieve payment reference code.
     *
     * @return string
     */
    public function getReferenceId() : string
    {
        return (string) $this->referenceId;
    }

    /**
     * Retrieve payment date
     *
     * @return Carbon|\Illuminate\Support\Carbon
     */
    public function getDate() : Carbon
    {
        return $this->date;
    }
}
