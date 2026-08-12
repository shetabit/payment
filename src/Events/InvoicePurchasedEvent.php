<?php

namespace Shetabit\Payment\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Shetabit\Multipay\Contracts\DriverInterface;
use Shetabit\Multipay\Invoice;

/**
 * Dispatched right after an invoice was purchased from a gateway.
 */
class InvoicePurchasedEvent
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * InvoicePurchasedEvent constructor.
     */
    public function __construct(
        public readonly DriverInterface $driver,
        public readonly Invoice $invoice,
    ) {
    }
}
