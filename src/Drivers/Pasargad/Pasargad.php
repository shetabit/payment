<?php

namespace Shetabit\Payment\Drivers\Pasargad;

use GuzzleHttp\Client;
use Shetabit\Payment\Invoice;
use Shetabit\Payment\Receipt;
use Shetabit\Payment\Abstracts\Driver;
use Shetabit\Payment\Contracts\ReceiptInterface;
use Shetabit\Payment\Exceptions\InvalidPaymentException;
use Shetabit\Payment\Drivers\Pasargad\Utils\RSAProcessor;

class Pasargad extends Driver
{
    /**
     * Guzzle client
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
     * Prepared invoice's data
     *
     * @var array
     */
    protected $preparedData = array();

    /**
     * Pasargad(PEP) constructor.
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
     */
    public function purchase()
    {
        $invoiceData = $this->getPreparedInvoiceData();

        $this->invoice->transactionId($invoiceData['InvoiceNumber']);

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
        $paymentUrl = $this->settings->apiPaymentUrl;
        $getTokenUrl = $this->settings->apiGetToken;
        $tokenData = $this->request($getTokenUrl, $this->getPreparedInvoiceData());

        // redirect using HTML form
        return $this->redirectWithForm($paymentUrl, $tokenData, 'POST');
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
        $invoiceDetails = $this->request(
            $this->settings->apiCheckTransactionUrl,
            [
                'TransactionReferenceID' => request()->input('tref')
            ]
        );

        $fields = [
            'MerchantCode' => $invoiceDetails['MerchantCode'],
            'TerminalCode' => $invoiceDetails['TerminalCode'],
            'InvoiceNumber' => $invoiceDetails['InvoiceNumber'],
            'InvoiceDate' => $invoiceDetails['InvoiceDate'],
            'Amount' => $invoiceDetails['Amount'],
            'Timestamp' => date("Y/m/d H:i:s"),
        ];

        $verifyResult = $this->request($this->settings->apiVerificationUrl, $fields);

        return $this->createReceipt($verifyResult, $invoiceDetails);
    }

    /**
     * Generate the payment's receipt
     *
     * @param $referenceId
     *
     * @return Receipt
     */
    protected function createReceipt($verifyResult, $invoiceDetails)
    {
        $referenceId = $invoiceDetails['TransactionReferenceID'];
        $traceNumber = $invoiceDetails['TraceNumber'];
        $referenceNumber = $invoiceDetails['ReferenceNumber'];

        $reciept = new Receipt('Pasargad', $referenceId);

        $reciept->detail('TraceNumber', $traceNumber);
        $reciept->detail('ReferenceNumber', $referenceNumber);
        $reciept->detail('MaskedCardNumber', $verifyResult['MaskedCardNumber']);
        $reciept->detail('ShaparakRefNumber', $verifyResult['ShaparakRefNumber']);

        return $reciept;
    }

    /**
     * A default message for exceptions
     *
     * @return string
     */
    protected function getDefaultExceptionMessage()
    {
        return 'مشکلی در دریافت اطلاعات از بانک به وجود آمده است';
    }

    /**
     * Sign given data.
     *
     * @param string $data
     *
     * @return string
     */
    public function sign($data)
    {
        $certificate = $this->settings->certificate;
        $certificateType = $this->settings->certificateType;

        $processor = new RSAProcessor($certificate, $certificateType);

        return $processor->sign($data);
    }

    /**
     * Retrieve prepared invoice's data
     *
     * @return array
     */
    protected function getPreparedInvoiceData()
    {
        if (empty($this->preparedData)) {
            $this->preparedData = $this->prepareInvoiceData();
        }

        return $this->preparedData;
    }

    /**
     * Prepare invoice data
     *
     * @return array
     */
    protected function prepareInvoiceData(): array
    {
        $action = 1003; // 1003 : for buy request (bank standard)
        $merchantCode = $this->settings->merchantId;
        $terminalCode = $this->settings->terminalCode;
        $amount = $this->invoice->getAmount(); //rial
        $redirectAddress = $this->settings->callbackUrl;
        $invoiceNumber = crc32($this->invoice->getUuid()) . rand(0, time());
        $timeStamp = date("Y/m/d H:i:s");
        $invoiceDate = date("Y/m/d H:i:s");

        if (!empty($this->invoice->getDetails()['date'])) {
            $invoiceDate = $this->invoice->getDetails()['date'];
        }

        return [
            'InvoiceNumber' => $invoiceNumber,
            'InvoiceDate' => $invoiceDate,
            'Amount' => $amount,
            'TerminalCode' => $terminalCode,
            'MerchantCode' => $merchantCode,
            'RedirectAddress' => $redirectAddress,
            'Timestamp' => $timeStamp,
            'Action' => $action,
        ];
    }

    /**
     * Prepare signature based on Pasargad document
     *
     * @param string $data
     * @return string
     */
    public function prepareSignature(string $data): string
    {
        return base64_encode($this->sign(sha1($data, true)));
    }

    /**
     * Make request to pasargad's Api
     *
     * @param string $url
     * @param array $body
     * @param string $method
     * @return array
     */
    protected function request(string $url, array $body, $method = 'POST'): array
    {
        $body = json_encode($body);
        $sign = $this->prepareSignature($body);

        $response = $this->client->request(
            'POST',
            $url,
            [
                'body' => $body,
                'headers' => [
                    'content-type' => 'application/json',
                    'Sign' => $sign
                ],
                "http_errors" => false,
            ]
        );

        $result = json_decode($response->getBody(), true);

        if ($result['IsSuccess'] === false) {
            throw new InvalidPaymentException($result['Message']);
        }

        return $result;
    }
}
