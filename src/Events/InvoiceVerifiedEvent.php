<?php

namespace Shetabit\Payment\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Shetabit\Multipay\Contracts\DriverInterface;
use Shetabit\Multipay\Contracts\ReceiptInterface;
use Shetabit\Multipay\Invoice;

/**
 * Dispatched right after a payment was verified with a gateway.
 */
class InvoiceVerifiedEvent
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * InvoiceVerifiedEvent constructor.
     */
    public function __construct(
        public readonly ReceiptInterface $receipt,
        public readonly DriverInterface $driver,
        public readonly Invoice $invoice,
    ) {
    }
}
