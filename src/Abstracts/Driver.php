<?php

namespace Shetabit\Payment\Abstracts;

use Shetabit\Payment\Contracts\DriverInterface;
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
     * Driver constructor.
     * @param Invoice $invoice
     * @param $settings
     */
    abstract public function __construct(Invoice $invoice, $settings);

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
     * @param $value|null
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
     * @param $url
     * @param array $data
     * @return string
     */
    public function createRedirectionForm($url, array $data)
    {
        $output = '<html><head><meta charset="utf-8" />';
        $output .= '<script>function pay() { document.forms["pay"].submit(); }</script>';
        $output .= '</head><body onload="pay();"><form name="pay" method="post" action="'.$url.'">';
        if ( !empty($data) ) {
            foreach ($data as $key => $value) {
                $output.='<input type="hidden" name="'.$key.'" value="'.$value.'">';
            }
        }
        $output.='<input type="submit" value="doing the payment...">';
        $output.='</form></body></html>';

        return $output;
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
     * @return mixed
     */
    abstract public function verify();
}
