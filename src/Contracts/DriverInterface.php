<?php

namespace Shetabit\Payment\Contracts;

interface DriverInterface
{
    /**
     * Set payment amount.
     *
     * @param $amount
     * @return $this
     * @throws \Exception
     */
    public function amount($amount);

    /**
     * Set a piece of data to the details.
     *
     * @param $key
     * @param null $value
     * @return $this
     */
    public function detail($key, $value = null);

    /**
     * Create new purchase
     *
     * @return $this
     */
    public function purchase();

    /**
     * Pay the purchase
     *
     * @return mixed
     */
    public function pay();

    /**
     * verify the payment
     *
     * @return object
     */
    public function verify();
}
