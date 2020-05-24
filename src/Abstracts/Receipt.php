<?php

namespace Shetabit\Payment\Abstracts;

use Carbon\Carbon;
use Shetabit\Payment\Contracts\ReceiptInterface;
use Shetabit\Payment\Exceptions\NoTransactionIdException;

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
     * The transaction ID that was created when initiating the payment.
     * This ID can be used to track transaction's state inside database.
     *
     * @var string|null
     */
    protected $transactionId;

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
     * @param string|null $transactionId
     */
    public function __construct($driver, $referenceId, $transactionId = null)
    {
        $this->driver = $driver;
        $this->referenceId = $referenceId;
        $this->transactionId = $transactionId;
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
     * Retrieve transaction's ID.
     *
     * @return string
     * @throws NoTransactionIdException
     */
    public function getTransactionId() : string
    {
        // Developer may be relaying on this, so instead of returning null
        // we'll throw an exception to let them know something's wrong
        if (empty($this->transactionId)) {
            throw new NoTransactionIdException();
        }
        return (string) $this->transactionId;
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
