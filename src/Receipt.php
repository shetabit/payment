<?php

namespace Shetabit\Payment;

use Carbon\Carbon;
use Shetabit\Payment\Traits\HasDetail;

class Receipt
{
    use HasDetail;

    /**
     * A unique ID which is given to the customer whenever the payment is done successfully.
     * This ID can be used for financial follow up.
     *
     * @var string
     */
    protected $referenceId;

    /**
     * payment gateway's name.
     *
     * @var string
     */
    protected $gateway;

    /**
     * date
     *
     * @var Carbon
     */
    protected $date;

    /**
     * Receipt constructor.
     *
     * @param $referenceId
     */
    public function __construct($gateway, $referenceId)
    {
        $this->gateway = $gateway;
        $this->referenceId = $referenceId;
        $this->date = now();
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

    /**
     * Add given value into details
     *
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->detail($name, $value);
    }

    /**
     * Retrieve given value from details
     *
     * @param $name
     */
    public function __get($name)
    {
        $this->getDetail($name);
    }
}
