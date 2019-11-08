<?php

namespace Shetabit\Payment\Drivers\Payping;

use GuzzleHttp\Client;
use Shetabit\Payment\Abstracts\Driver;
use Shetabit\Payment\Exceptions\InvalidPaymentException;
use Shetabit\Payment\Exceptions\PurchaseFailedException;
use Shetabit\Payment\Contracts\ReceiptInterface;
use Shetabit\Payment\Invoice;
use Shetabit\Payment\Receipt;

class Payping extends Driver
{
    /**
     * Payping Client.
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
     * Payping constructor.
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
        $name = $this->extractDetails('name');
        $mobile = $this->extractDetails('mobile');
        $email = $this->extractDetails('email');
        $description = $this->extractDetails('description');

        $data = array(
            "payerName" => $name,
            "amount" => $this->invoice->getAmount(),
            "payerIdentity" => $mobile ?? $email,
            "returnUrl" => $this->settings->callbackUrl,
            "description" => $description,
            "clientRefId" => $this->invoice->getUuid(),
        );

        $response = $this
            ->client
            ->request(
                'POST',
                $this->settings->apiPurchaseUrl,
                [
                    "json" => $data,
                    "headers" => [
                        "Accept" => "application/json",
                        "Authorization" => "bearer ".$this->settings->merchantId,
                    ],
                    "http_errors" => false,
                ]
            );
        $body = json_decode($response->getBody()->getContents(), true);

        if (!empty($body['Error'])) {
            // some error has happened
            throw new PurchaseFailedException($body['Error']);
        }

        $this->invoice->transactionId($body['code']);

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
        $refId = request()->input('refid');
        $data = [
            'amount' => $this->invoice->getAmount(),
            'refId'  => $refId,
        ];

        $response = $this->client->request(
            'POST',
            $this->settings->apiVerificationUrl,
            [
                'json' => $data,
                "headers" => [
                    "Accept" => "application/json",
                    "Authorization" => "bearer ".$this->settings->merchantId,
                ],
                "http_errors" => false,
            ]
        );

        $responseBody = mb_strtolower($response->getBody()->getContents());
        dd($responseBody);

        $body = @json_decode($responseBody, true);

        if (!empty($body['amount']) || !empty($body['refid']) || !empty($body['error'])) {
            $this->notVerified($body);
        }

        return $this->createReceipt($refId);
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
        $receipt = new Receipt('payping', $referenceId);

        return $receipt;
    }

    /**
     * Trigger an exception
     *
     * @param $status
     * @throws InvalidPaymentException
     */
    private function notVerified($status)
    {
        $message = $status['amount'] ?? $status['refid'] ?? $status['error'];

        throw new InvalidPaymentException($message);
    }
}
