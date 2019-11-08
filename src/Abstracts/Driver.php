<?php

namespace Shetabit\Payment\Abstracts;

use Shetabit\Payment\Contracts\DriverInterface;
use Shetabit\Payment\Contracts\ReceiptInterface;
use Shetabit\Payment\Invoice;

abstract class Driver implements DriverInterface
{
    /**
     * Invoice
     *
     * @var Invoice
     */
    protected $invoice;

    /**
     * Driver's settings
     *
     * @var
     */
    protected $settings;

    /**
     * Driver constructor.
     *
     * @param Invoice $invoice
     * @param $settings
     */
    abstract public function __construct(Invoice $invoice, $settings);

    /**
     * Set payment amount.
     *
     * @param $amount
     *
     * @return $this
     *
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
     * @param $value|null
     *
     * @return $this|DriverInterface
     */
    public function detail($key, $value = null)
    {
        $key = is_array($key) ? $key : [$key => $value];

        foreach ($key as $k => $v) {
            $this->invoice->detail($key, $value);
        }

        return $this;
    }

    /**
     * Set invoice.
     *
     * @param Invoice $invoice
     *
     * @return $this
     */
    public function invoice(Invoice $invoice)
    {
        $this->invoice = $invoice;

        return $this;
    }

    /**
     * Retrieve invoice.
     *
     * @return Invoice
     */
    public function getInvoice()
    {
        return $this->invoice;
    }

    /**
     * Create payment redirection form.
     *
     * @param $action
     * @param array $inputs
     * @param string $method
     *
     * @return string
     */
    public function redirectWithForm($action, array $inputs = [], $method = 'POST')
    {
        return view('shetabitPayment::redirectForm')->with(
            [
                'action' => $action,
                'inputs' => $inputs,
                'method' => $method,
            ]
        );
    }

    /**
     * Purchase the invoice
     *
     * @return string
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
     * @return ReceiptInterface
     */
    abstract public function verify() : ReceiptInterface;
}
