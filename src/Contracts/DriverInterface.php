<?php

namespace Shetabit\Payment\Contracts;

interface DriverInterface
{
    /**
     * Set payment amount.
     *
     * @param $amount
     *
     * @return $this
     *
     * @throws \Exception
     */
    public function amount($amount);

    /**
     * Set a piece of data to the details.
     *
     * @param $key
     * @param $value|null
     *
     * @return mixed
     */
    public function detail($key, $value = null);

    /**
     * Create new purchase
     *
     * @return string
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
     * @return ReceiptInterface
     */
    public function verify() : ReceiptInterface;
}
