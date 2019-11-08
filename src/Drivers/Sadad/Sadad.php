<?php

namespace Shetabit\Payment\Drivers\Sadad;

use GuzzleHttp\Client;
use Shetabit\Payment\Abstracts\Driver;
use Shetabit\Payment\Exceptions\InvalidPaymentException;
use Shetabit\Payment\Exceptions\PurchaseFailedException;
use Shetabit\Payment\Contracts\ReceiptInterface;
use Shetabit\Payment\Invoice;
use Shetabit\Payment\Receipt;

class Sadad extends Driver
{
    /**
     * Sadad Client.
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
     * Sadad constructor.
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
        $terminalId = $this->settings->terminalId;
        $orderId = crc32($this->invoice->getUuid());
        $amount = $this->invoice->getAmount() * 10; // convert to rial
        $key = $this->settings->key;

        $signData = $this->encrypt_pkcs7("$terminalId;$orderId;$amount", $key);

        $data = array(
            'MerchantId' => $this->settings->merchantId,
            'ReturnUrl' => $this->settings->callbackUrl,
            'LocalDateTime' => date("m/d/Y g:i:s a"),
            'SignData' => $signData,
            'TerminalId' => $terminalId,
            'Amount' => $amount,
            'OrderId' => $orderId,
        );

        $response = $this
            ->client
            ->request(
                'POST',
                $this->settings->apiPurchaseUrl,
                [
                    "json" => $data,
                    "headers" => [
                        'Content-Type' => 'application/json',
                    ],
                    "http_errors" => false,
                ]
            );

        $body = @json_decode($response->getBody()->getContents(), true);

        if (empty($body)) {
            throw new PurchaseFailedException('دسترسی به صفحه مورد نظر امکان پذیر نمی باشد.');
        } elseif ($body->ResCode != 0) {
            throw new PurchaseFailedException($body->Description);
        }

        $this->invoice->transactionId($body->Token);

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
        $token = $this->invoice->getTransactionId();
        $payUrl = $this->settings->apiPaymentUrl.'?Token='.$token;

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
        $key = $this->settings->key;
        $token = $this->invoice->getTransactionId() ?? request()->get('token');
        $resCode = request()->get('ResCode');
        $message = 'تراکنش نا موفق بود در صورت کسر مبلغ از حساب شما حداکثر پس از 72 ساعت مبلغ به حسابتان برمیگردد.';

        if ($resCode == 0) {
            throw new InvalidPaymentException($message);
        }

        $data = array(
            'Token' => $token,
            'SignData' => $this->encrypt_pkcs7($token, $key)
        );

        $response = $this
            ->client
            ->request(
                'POST',
                $this->settings->apiPurchaseUrl,
                [
                    "json" => $data,
                    "headers" => [
                        'Content-Type' => 'application/json',
                    ],
                    "http_errors" => false,
                ]
            );

        $body = json_decode($response->getBody()->getContents(), true);

        if ($body->ResCode == -1) {
            throw new InvalidPaymentException($message);
        }

        /**
         * شماره سفارش : $orderId = request()->get('OrderId')
         * شماره پیگیری : $body->SystemTraceNo
         * شماره مرجع : $body->RetrievalRefNo
         */

        return $this->createReceipt($body->SystemTraceNo);
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
        $receipt = new Receipt('sadad', $referenceId);

        return $receipt;
    }

    /**
     * Create sign data(Tripledes(ECB,PKCS7))
     *
     * @param $str
     * @param $key
     *
     * @return string
     */
    protected function encrypt_pkcs7($str, $key)
    {
        $key = base64_decode($key);
        $ciphertext = OpenSSL_encrypt($str, "DES-EDE3", $key, OPENSSL_RAW_DATA);

        return base64_encode($ciphertext);
    }
}
