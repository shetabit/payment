<?php

namespace Shetabit\Payment\Drivers\Pasargad;

use Shetabit\Payment\Abstracts\Driver;
use Shetabit\Payment\Exceptions\InvalidPaymentException;
use Shetabit\Payment\Exceptions\PurchaseFailedException;
use Shetabit\Payment\Contracts\ReceiptInterface;
use Shetabit\Payment\Drivers\Pasargad\Utils\RSAProcessor;
use Shetabit\Payment\Invoice;
use Shetabit\Payment\Receipt;

class Pasargad extends Driver
{
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
    }

    /**
     * Purchase Invoice.
     *
     * @return string
     */
    public function purchase()
    {
        $invoiceData = $this->getPreparedInvoiceData();

        $this->invoice->transactionId($invoiceData['signed']);

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
        $data = array_merge($this->getPreparedInvoiceData(), ['submit' => 'Checkout']);

        // redirect using HTML form
        return $this->redirectWithForm($paymentUrl, $data, 'POST');
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
        $client = new Client();

        $response = $client
            ->request(
                'POST',
                $this->settings->apiCheckTransactionUrl,
                [
                    "form_params" => ['invoiceUID' => request()->input('tref')],
                    "http_errors" => false,
                ]
            );

        $invoiceDetails = $this->makeXMLTree($response->getBody()->getContents());
        $referenceId = $invoiceDetails['resultObj']['transactionReferenceID'];
        $traceNumber = $invoiceDetails['resultObj']['traceNumber'];
        $referenceNumber = $invoiceDetails['resultObj']['referenceNumber'];

        $invoiceData = $this->getPreparedInvoiceData();
        $fields = array(
            'InvoiceNumber' => request()->input('iN'),
            'InvoiceDate' => request()->input('tref'),
            'MerchantCode' => $invoiceData['merchantCode'],
            'TerminalCode' => $invoiceData['terminalCode'],
            'amount' => $invoiceData['amount'],
            'TimeStamp' => $invoiceData['timeStamp'],
            'sign' => $invoiceData['sign']
        );


        $response = $client
            ->request(
                'POST',
                $this->settings->apiVerificationUrl,
                [
                    "form_params" => $fields,
                    "http_errors" => false,
                ]
            );
        $verifyResult = $this->makeXMLTree($response->getBody()->getContents());

        if (empty($verifyResult['actionResult']) || is_null($verifyResult['actionResult']['result'])) {
            throw new InvalidPaymentException($this->getDefaultExceptionMessage());
        }

        if ($verifyResult['actionResult']['result'] === false) {
            throw new InvalidPaymentException($verifyResult['actionResult']['resultMessage'] ?? $this->getDefaultExceptionMessage());
        }

        return $this->createReceipt($referenceId, $traceNumber, $referenceNumber);
    }

    /**
     * Generate the payment's receipt
     *
     * @param $referenceId
     *
     * @return Receipt
     */
    protected function createReceipt($referenceId, $traceNumber, $referenceNumber)
    {
        $reciept = new Receipt('Pasargad', $referenceId);

        $reciept->detail('trace_number', $traceNumber);
        $reciept->detail('reference_number', $referenceNumber);

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
    protected function prepareInvoiceData()
    {
        $action = "1003"; 	// 1003 : for buy request (bank standard)
        $merchantCode = $this->settings->merchantId;
        $terminalCode = $this->settings->terminalCode;
        $amount = $this->invoice->getAmount() * 10; // convert to toman
        $redirectAddress = $this->settings->callbackUrl; 
        $invoiceNumber = crc32($this->invoice->getUuid()).rand(0, time());
        $timeStamp = date("Y/m/d H:i:s");

        if (!empty($this->invoice->getDetails()['date'])) {
            $invoiceDate = $this->invoice->getDetails()['date'];
        } else {
            $invoiceDate = date("Y/m/d H:i:s");
        }

        $data = "#". $merchantCode ."#". $terminalCode ."#". $invoiceNumber ."#". $invoiceDate ."#". $amount ."#". $redirectAddress ."#". $action ."#". $timeStamp ."#";
        $data = sha1($data,true);
        $data =  $this->sign($data);
        $signedData =  base64_encode($data);

        return [
            'invoiceNumber' => $invoiceNumber,
            'invoiceDate' => $invoiceDate,
            'amount' => $amount,
            'terminalCode' => $terminalCode,
            'merchantCode' => $merchantCode,
            'redirectAddress' => $redirectAddress,
            'timeStamp' => $timeStamp,
            'action' => $action,
            'signed' => $signedData,
        ];
    }

    /**
     * Convert XML tree to array
     *
     * @param $data
     *
     * @return array
     */
    protected function makeXMLTree($data)
    {
       $ret = array();

       $parser = xml_parser_create();
       xml_parser_set_option($parser,XML_OPTION_CASE_FOLDING,0);
       xml_parser_set_option($parser,XML_OPTION_SKIP_WHITE,1);
       xml_parse_into_struct($parser,$data,$values,$tags);
       xml_parser_free($parser);

       $hash_stack = array();
       foreach ($values as $key => $val) {
          switch ($val['type']) {
             case 'open':
                array_push($hash_stack, $val['tag']);
             break;
             case 'close':
                array_pop($hash_stack);
             break;
             case 'complete':
                array_push($hash_stack, $val['tag']);
                // uncomment to see what this function is doing
                // echo("\$ret[" . implode($hash_stack, "][") . "] = '{$val[value]}';\n");
                eval("\$ret[" . implode($hash_stack, "][") . "] = '{$val[value]}';");
                array_pop($hash_stack);
             break;
          }
       }

       return $ret;
    }  
}
