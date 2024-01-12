<?php

namespace Shetabit\Payment\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Shetabit\Multipay\Contracts\DriverInterface;
use Shetabit\Multipay\Contracts\ReceiptInterface;
use Shetabit\Multipay\Invoice;

class InvoiceVerifiedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $receipt;
    public $driver;
    public $invoice;

    /**
     * InvoiceVerifiedEvent constructor.
     *
     * @param ReceiptInterface $receipt
     * @param DriverInterface $driver
     * @param Invoice $invoice
     */
    public function __construct(ReceiptInterface $receipt, DriverInterface $driver, Invoice $invoice)
    {
        $this->receipt = $receipt;
        $this->driver = $driver;
        $this->invoice = $invoice;
    }
}
