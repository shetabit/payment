<?php

namespace Shetabit\Payment\Abstracts;

use Shetabit\Payment\Contracts\DriverInterface;

abstract class Driver implements DriverInterface
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
     * @var array
     */
    protected $details = [];

    /**
     * Driver constructor.
     *
     * @param $settings
     */
    abstract public function __construct($settings);

    /**
     * Set payment amount.
     *
     * @param $amount
     * @return $this
     * @throws \Exception
     */
    public function amount($amount)
    {
        if (! is_int($amount)) {
            throw new \Exception('Amount value should be an integer.');
        }
        $this->body = $amount;

        return $this;
    }

    /**
     * Set a piece of data to the details.
     *
     * @param $key
     * @param null $value
     * @return $this
     */
    public function with($key, $value = null)
    {
        $key = is_array($key) ? $key : [$key => $value];

        foreach ($key as $k => $v) {
            $this->details[$k] = $v;
        }

        return $this;
    }

    /**
     * Create new purchase
     *
     * @return mixed
     */
    abstract public function purchase();

    /**
     * Pay the purchase
     *
     * @return mixed
     */
    abstract public function pay();

    /**
     * Verify the payment
     *
     * @return object
     */
    abstract public function verify();
}
