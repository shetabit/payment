<?php

namespace Shetabit\Payment;

class PaymentBuilder
{
    /**
     * Amount
     *
     * @var int
     */
    protected $amount = 0;

    /**
     * Payment details
     *
     * @var string
     */
    protected $details = [];

    /**
     * @var string
     */
    protected $driver;

    /**
     * Set a piece of data to the details.
     *
     * @param $key
     * @param null $value
     * @return $this
     */
    public function detail($key, $value = null)
    {
        $key = is_array($key) ? $key : [$key => $value];

        foreach ($key as $k => $v) {
            $this->details[$k] = $v;
        }

        return $this;
    }

    /**
     * Get the value of details
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * Set the value of recipients
     *
     * @param $amount
     * @return $this
     */
    public function amount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get the value of body
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Get the value of driver
     */
    public function getDriver()
    {
        return $this->driver;
    }

    /**
     * Set the value of driver
     *
     * @param $driver
     * @return $this
     */
    public function via($driver)
    {
        $this->driver = $driver;

        return $this;
    }
}
