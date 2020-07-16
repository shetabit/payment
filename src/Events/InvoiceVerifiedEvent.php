<?php

namespace Shetabit\Payment\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
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
