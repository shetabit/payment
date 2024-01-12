<?php

namespace Shetabit\Payment\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Shetabit\Multipay\Contracts\DriverInterface;
use Shetabit\Multipay\Invoice;

class InvoicePurchasedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $driver;
    public $invoice;

    /**
     * InvoicePurchasedEvent constructor.
     *
     * @param DriverInterface $driver
     * @param Invoice $invoice
     */
    public function __construct(DriverInterface $driver, Invoice $invoice)
    {
        $this->driver = $driver;
        $this->invoice = $invoice;
    }
}
