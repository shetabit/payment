<?php

namespace Shetabit\Payment\Drivers\Nextpay;

use GuzzleHttp\Client;
use Shetabit\Payment\Abstracts\Driver;
use Shetabit\Payment\Exceptions\InvalidPaymentException;
use Shetabit\Payment\Exceptions\PurchaseFailedException;
use Shetabit\Payment\Contracts\ReceiptInterface;
use Shetabit\Payment\Invoice;
use Shetabit\Payment\Receipt;

class Nextpay extends Driver
{
    /**
     * Nextpay Client.
     *
     * @var Client
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
     * Nextpay constructor.
     * Construct the class with the relevant settings.
     *
     * @param Invoice $invoice
     * @param $settings
     */
    public function __construct(Invoice $invoice, $settings)
    {
        $this->invoice($invoice);
        $this->settings = (object) $settings;
        $this->client = new Client();
    }

    /**
     * Purchase Invoice.
     *
     * @return string
     *
     * @throws PurchaseFailedException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function purchase()
    {
        $data = array(
            'api_key' => $this->settings->merchantId,
            'order_id' => intval(1, time()).crc32($this->invoice->getUuid()),
            'amount' => $this->invoice->getAmount(),
            'callback_uri' => $this->settings->callbackUrl,
        );

        $response = $this
            ->client
            ->request(
                'POST',
                $this->settings->apiPurchaseUrl,
                [
                    "form_params" => $data,
                    "http_errors" => false,
                ]
            );

        $body = json_decode($response->getBody()->getContents(), true);

        if (empty($body['code']) || $body['code'] != -1) {
            // error has happened
            throw new PurchaseFailedException($body['message']);
        }

        $this->invoice->transactionId($body['trans_id']);

        // return the transaction's id
        return $this->invoice->getTransactionId();
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
     * @return ReceiptInterface
     *
     * @throws InvalidPaymentException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function verify() : ReceiptInterface
    {
        $transactionId = $this->invoice->getTransactionId() ?? request()->input('trans_id');

        $data = [
            'api_key' => $this->settings->merchantId,
            'order_id' => request()->input('order_id'),
            'amount' => $this->invoice->getAmount(),
            'trans_id' => $transactionId,
        ];

        $response = $this
            ->client
            ->request(
                'POST',
                $this->settings->apiVerificationUrl,
                [
                    "form_params" => $data,
                    "http_errors" => false,
                ]
            );

        $body = json_decode($response->getBody()->getContents(), true);

        if (!isset($body['code']) || $body['code'] != 0) {
            $message = $body['message'] ?? 'خطای ناشناخته ای رخت داده است';

            throw new InvalidPaymentException($message);
        }

        return $this->createReceipt($transactionId);
    }

    /**
     * Generate the payment's receipt
     *
     * @param $referenceId
     *
     * @return Receipt
     */
    protected function createReceipt($referenceId)
    {
        $receipt = new Receipt('nextpay', $referenceId);

        return $receipt;
    }
}
