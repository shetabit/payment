<?php

namespace Shetabit\Payment\Drivers\Zibal;

use GuzzleHttp\Client;
use Shetabit\Payment\Abstracts\Driver;
use Shetabit\Payment\Exceptions\InvalidPaymentException;
use Shetabit\Payment\Exceptions\PurchaseFailedException;
use Shetabit\Payment\Contracts\ReceiptInterface;
use Shetabit\Payment\Invoice;
use Shetabit\Payment\Receipt;

class Zibal extends Driver
{
    /**
     * Zibal Client.
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
     * Zibal constructor.
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
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function purchase()
    {
        $details = $this->invoice->getDetails();

        // convert to toman
        $toman = $this->invoice->getAmount() * 10;

        if (!empty($details['orderId'])) {
            $orderId = $details['orderId'];
        } elseif (!empty($details['order_id'])) {
            $orderId = $details['order_id'];
        } else {
            $orderId = crc32($this->invoice->getUuid()).time();
        }

        $mobile = null;
        if (!empty($details['mobile'])) {
            $mobile = $details['mobile'];
        } elseif (!empty($details['phone'])) {
            $mobile = $details['phone'];
        }

        $data = array(
            "merchant"=> $this->settings->merchantId, //required
            "callbackUrl"=> $this->settings->callbackUrl, //required
            "amount"=> $toman, //required
            "orderId"=> $orderId, //optional
            'mobile' => $mobile, //optional for mpg
        );

        $json = json_encode($data, JSON_UNESCAPED_UNICODE);

        $response = $this->client->request(
            'POST',
            $this->settings->apiPurchaseUrl,
            [
                "form_params" => $json,
                "http_errors" => false,
            ]
        );
        $body = json_decode($response->getBody()->getContents(), true);

        if ($response->result != 100) {
            // some error has happened
            throw new PurchaseFailedException($response->message);
        } else {
            $this->invoice->transactionId($response->trackId);
        }

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
     * @return mixed|void
     *
     * @throws InvalidPaymentException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function verify() : ReceiptInterface
    {
        $successFlag = request()->input('success');
        $orderId = request()->input('orderId');
        $transactionId = $this->invoice->getTransactionId() ?? request()->input('trackId');

        if (!$successFlag != 1) {
            $this->notVerified('پرداخت با شکست مواجه شد');
        }

        //start verfication
        $data = array(
            "merchant" => $this->settings->merchantId, //required
            "trackId" => $transactionId, //required
        );

        $json = json_encode($data, JSON_UNESCAPED_LINE_TERMINATORS);

        $response = $this->client->request(
            'POST',
            $this->settings->apiVerificationUrl,
            ["form_params" => $json, "http_errors" => false]
        );
        $body = json_decode($response->getBody()->getContents(), true);

        if ($body->result != 100) {
            $this->notVerified($body->message);
        }

        /*
            for more info:
            var_dump($body);
        */

        return $this->createReceipt($orderId);
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
        $receipt = new Receipt('Zibal', $referenceId);

        return $receipt;
    }

    /**
     * Trigger an exception
     *
     * @param $message
     * @throws InvalidPaymentException
     */
    private function notVerified($message)
    {
        if (empty($message)) {
            throw new InvalidPaymentException('خطای ناشناخته ای رخ داده است.');
        } else {
            throw new InvalidPaymentException($message);
        }
    }
}
