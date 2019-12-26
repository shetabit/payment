<?php

namespace Shetabit\Payment\Drivers\Payir;

use GuzzleHttp\Client;
use Shetabit\Payment\Abstracts\Driver;
use Shetabit\Payment\Exceptions\InvalidPaymentException;
use Shetabit\Payment\Exceptions\PurchaseFailedException;
use Shetabit\Payment\Contracts\ReceiptInterface;
use Shetabit\Payment\Invoice;
use Shetabit\Payment\Receipt;

class Payir extends Driver
{
    /**
     * Payir Client.
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
     * Payir constructor.
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
     * Retrieve data from details using its name.
     *
     * @return string
     */
    private function extractDetails($name)
    {
        return empty($this->invoice->getDetails()[$name]) ? null : $this->invoice->getDetails()[$name];
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
        $mobile = $this->extractDetails('mobile');
        $description = $this->extractDetails('description');
        $validCardNumber = $this->extractDetails('validCardNumber');

        $data = array(
            'api' => $this->settings->merchantId,
            'amount' => $this->invoice->getAmount(),
            'redirect' => $this->settings->callbackUrl,
            'mobile' => $mobile,
            'description' => $description,
            'factorNumber' => $this->invoice->getUuid(),
            'validCardNumber' => $validCardNumber
        );

        $response = $this->client->request(
            'POST',
            $this->settings->apiPurchaseUrl,
            [
                "form_params" => $data,
                "http_errors" => false,
            ]
        );
        $body = json_decode($response->getBody()->getContents(), true);

        if ($body['status'] != 1) {
            // some error has happened
            throw new PurchaseFailedException($body['errorMessage']);
        }

        $this->invoice->transactionId($body['token']);

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
        $data = [
            'api' => $this->settings->merchantId,
            'token'  => $this->invoice->getTransactionId() ?? request()->input('token'),
        ];

        $response = $this->client->request(
            'POST',
            $this->settings->apiVerificationUrl,
            [
                "form_params" => $data,
                "http_errors" => false,
            ]
        );
        $body = json_decode($response->getBody()->getContents(), true);

        if ($body['status'] != 1) {
            $this->notVerified($body['errorCode']);
        }

        return $this->createReceipt($body['transId']);
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
        $receipt = new Receipt('payir', $referenceId);

        return $receipt;
    }

    /**
     * Trigger an exception
     *
     * @param $status
     *
     * @throws InvalidPaymentException
     */
    private function notVerified($status)
    {
        $translations = array(
            "-1" => "ارسال api الزامی می باشد",
            "-2" => "ارسال transId الزامی می باشد",
            "-3" => "درگاه پرداختی با api ارسالی یافت نشد و یا غیر فعال می باشد",
            "-4" => "فروشنده غیر فعال می باشد",
            "-5" => "تراکنش با خطا مواجه شده است",
        );

        if (array_key_exists($status, $translations)) {
            throw new InvalidPaymentException($translations[$status]);
        } else {
            throw new InvalidPaymentException('خطای ناشناخته ای رخ داده است.');
        }
    }
}
