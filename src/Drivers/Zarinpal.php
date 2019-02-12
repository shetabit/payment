<?php

namespace Shetabit\Payment\Drivers;

use GuzzleHttp\Client;
use Shetabit\Payment\Abstracts\Driver;
use Shetabit\Payment\InvoiceBuilder;

class Zarinpal extends Driver
{
    /**
     * Irankish Client.
     *
     * @var object
     */
    protected $client;

    /**
     * @var InvoiceBuilder
     */
    protected $invoice;

    /**
     * Driver settings
     *
     * @var object
     */
    protected $settings;

    /**
     * Construct the class with the relevant settings.
     *
     * Irankish constructor.
     * @param InvoiceBuilder $invoice
     * @param $settings
     */
    public function __construct(InvoiceBuilder $invoice, $settings)
    {
        $this->invoice = $invoice;
        $this->settings = (object) $settings;
        $this->client = new Client();
    }

    public function purchase()
    {
        $data = array(
            'MerchantID' => $this->settings->merchantId,
            'Amount' => $this->invoice->getAmount(),
            'CallbackURL' => $this->settings->callbackUrl,
            'Description' => $this->invoice->getDetails(),
            'AdditionalData' => $this->invoice->getDetails()
        );

        $response = $this->client->request(
            'POST',
            $this->settings->apiPurchaseUrl,
            ['json' => $data]
        );
        $body = json_decode($response->getBody()->getContents(), true);

        if (empty($body['Authority'])) {
            $body['Authority'] = null;
        } else {
            $this->invoice->setTransactionId($body['Authority']);
        }

        return $body;
    }

    public function pay()
    {
        $payUrl = $this->settings->apiPaymentUrl.$this->invoice->getTransactionId();
        return redirect()->url($payUrl);
    }

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

        if ($body['Status'] == 100) {
            return [
                'Status'      => 'success',
                'RefID'       => $body['RefID'],
                'ExtraDetail' => $body['ExtraDetail'],
            ];
        } elseif ($body['Status'] == 101) {
            return [
                'Status'      => 'verified_before',
                'RefID'       => $body['RefID'],
                'ExtraDetail' => $body['ExtraDetail'],
            ];
        } else {
            return [
                'Status'    => 'error',
                'error'     => !empty($body['Status']) ? $body['Status'] : null,
                'errorInfo' => !empty($body['errors']) ? $body['errors'] : null,
            ];
        }
    }
}
