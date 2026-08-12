<?php

namespace Shetabit\Payment\Tests\Drivers;

use Shetabit\Multipay\Abstracts\Driver;
use Shetabit\Multipay\Contracts\ReceiptInterface;
use Shetabit\Multipay\Invoice;
use Shetabit\Multipay\Receipt;
use Shetabit\Multipay\RedirectionForm;

/**
 * A driver that answers like a gateway would, without talking to one.
 *
 * It hands the settings it was constructed with back through the inputs of its
 * redirection form, so that the tests can tell which configuration the payment
 * manager handed over to it.
 */
class BarDriver extends Driver
{
    public const string DRIVER_NAME = 'bar';

    public const string GATEWAY_URL = 'https://example.com/gateway';

    public const string TRANSACTION_ID = 'random_transaction_id';

    public const string REFERENCE_ID = 'random_reference_id';

    /**
     * @param array<string, mixed>|object $settings
     */
    public function __construct(Invoice $invoice, array|object $settings)
    {
        $this->invoice($invoice);
        $this->settings = (object) $settings;
    }

    public function purchase() : string
    {
        $this->invoice->transactionId(self::TRANSACTION_ID);

        return self::TRANSACTION_ID;
    }

    public function pay() : RedirectionForm
    {
        /** @var array<string, mixed> $settings */
        $settings = (array) $this->settings;

        return $this->redirectWithForm(
            self::GATEWAY_URL,
            [
                'amount' => $this->invoice->getAmount(),
                'callbackUrl' => $settings['callbackUrl'],
            ],
            'POST'
        );
    }

    public function verify() : ReceiptInterface
    {
        return new Receipt(self::DRIVER_NAME, self::REFERENCE_ID);
    }
}
