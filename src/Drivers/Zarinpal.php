<?php

namespace Shetabit\Payment\Drivers;

use GuzzleHttp\Client;
use Shetabit\Payment\Abstracts\Driver;
use Shetabit\Payment\Exceptions\InvalidPaymentException;
use Shetabit\Payment\Invoice;

class Zarinpal extends Driver
{
    /**
     * Irankish Client.
     *
     * @var object
     */
    protected $client;

    /**
     * Invoice
     *
     * @var Invoice
     */
    protected $invoice;

    /**
     * Driver settings
     *
     * @var object
     */
    protected $settings;

    /**
     * Zarinpal constructor.
     * Construct the class with the relevant settings.
     *
     * @param Invoice $invoice
     * @param $settings
     */
    public function __construct(Invoice $invoice, $settings)
    {
        $this->setInvoice($invoice);
        $this->settings = (object) $settings;
        $this->client = new Client();
    }

    /**
     * Purchase Invoice.
     *
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function purchase()
    {
        if (!empty($this->invoice->getDetails()['description'])) {
            $description = $this->invoice->getDetails()['description'];
        } else {
            $description = $this->settings->description;
        }

        $data = array(
            'MerchantID' => $this->settings->merchantId,
            'Amount' => $this->invoice->getAmount(),
            'CallbackURL' => $this->settings->callbackUrl,
            'Description' => $description,
            'AdditionalData' => $this->invoice->getDetails()
        );

        $response = $this->client->request(
            'POST',
            $this->settings->apiPurchaseUrl,
            [
                "json" => $data,
            ]
        );
        $body = json_decode($response->getBody()->getContents(), true);

        if (empty($body['Authority'])) {
            $body['Authority'] = null;
        } else {
            $this->invoice->transactionId($body['Authority']);
        }

        return $body;
    }

    /**
     * Pay the Invoice
     *
     * @return \Illuminate\Http\RedirectResponse|mixed
     */
    public function pay()
    {
        $payUrl = $this->settings->apiPaymentUrl.$this->invoice->getTransactionId();

        // redirect using laravel logic
        return redirect()->to($payUrl);
    }

    /**
     * Verify payment
     *
     * @return mixed|void
     * @throws InvalidPaymentException
     */
    public function verify()
    {
        $data = [
            'MerchantID' => $this->settings->merchantId,
            'Authority'  => $this->invoice->getTransactionId(),
            'Amount' => $this->invoice->getAmount(),
        ];

        $response = $this->client->request(
            'POST',
            $this->settings->apiVerificationUrl,
            ['json' => $data]
        );
        $body = json_decode($response->getBody()->getContents(), true);

        // throw an exception when payment has some issues!
        if ($body['Status'] == 101) {
            throw new InvalidPaymentException('payment has been verified before');
        } else {
            throw new InvalidPaymentException($body['errors']);
        }
    }
}
