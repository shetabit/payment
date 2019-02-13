<?php

namespace Shetabit\Payment\Abstracts;

use Shetabit\Payment\Contracts\DriverInterface;
use Shetabit\Payment\InvoiceBuilder;

abstract class Driver implements DriverInterface
{
    /**
     * Invoice
     *
     * @var InvoiceBuilder
     */
    protected $invoice;

    /**
     * Driver constructor.
     *
     * Driver constructor.
     * @param InvoiceBuilder $invoice
     * @param $settings
     */
    abstract public function __construct(InvoiceBuilder $invoice, $settings);

    /**
     * Set payment amount.
     *
     * @param $amount
     * @return $this
     * @throws \Exception
     */
    public function amount($amount)
    {
        $this->invoice->amount($amount);

        return $this;
    }

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
            $this->invoice->detail($key, $value);
        }

        return $this;
    }

    public function setInvoice(InvoiceBuilder $invoice) {
        $this->invoice = $invoice;

        return $this;
    }

    public function getInvoice()
    {
        return $this->invoice;
    }

    /**
     * Purchase the invoice
     *
     * @return mixed
     */
    abstract public function purchase();

    /**
     * Pay the invoice
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
