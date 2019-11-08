<?php

namespace Shetabit\Payment\Drivers\Asanpardakht;

use Shetabit\Payment\Abstracts\Driver;
use Shetabit\Payment\Exceptions\InvalidPaymentException;
use Shetabit\Payment\Exceptions\PurchaseFailedException;
use Shetabit\Payment\Contracts\ReceiptInterface;
use Shetabit\Payment\Invoice;
use Shetabit\Payment\Receipt;

class Asanpardakht extends Driver
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
     * Asanpardakht constructor.
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
     *
     * @throws PurchaseFailedException
     * @throws \SoapFault
     */
    public function purchase()
    {
        $client = $this->createSoapClient($this->settings->apiPurchaseUrl);

        $params = $this->preparePurchaseData();
        $result = $client->RequestOperation($params);
        if (!$result) {
            throw  new PurchaseFailedException('خطای فراخوانی متد درخواست تراکنش.');
        }

        $result = $result->RequestOperationResult;
        if ($result{0} != '0') {
            $message = "خطای شماره ".$result." رخ داده است.";
            throw  new PurchaseFailedException($message);
        }

        $this->invoice->transactionId(substr($result, 2));

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
        $payUrl = $this->settings->apiPaymentUrl;

        return $this->redirectWithForm(
            $payUrl,
            ['RefId' => $this->invoice->getTransactionId()],
            'POST'
        );
    }

    /**
     * Verify payment
     *
     * @return mixed|Receipt
     *
     * @throws InvalidPaymentException
     * @throws \SoapFault
     */
    public function verify() : ReceiptInterface
    {
        $encryptedReturningParamsString = request()->get('ReturningParams');
        $returningParamsString = decrypt($encryptedReturningParamsString);
        $returningParams = explode(",", $returningParamsString);

        /**
         * other data:
         *   $amount = $returningParams[0];
         *   $saleOrderId = $returningParams[1];
         *   $refId = $this->invoice->getTransactionId() ?? $returningParams[2];
         *   $resMessage = $returningParams[4];
         *   $rrn = $returningParams[6];
         *   $lastFourDigitOfPAN = $returningParams[7];
         **/

        $resCode = $returningParams[3];
        $payGateTranID = $returningParams[5];

        if ($resCode != '0' && $resCode != '00') {
            $message = "خطای شماره ".$resCode." رخ داده و تراکنش ناموفق بوده است.";
            throw new InvalidPaymentException($message);
        }

        $client = $this->createSoapClient($this->settings->apiVerificationUrl);
        $params = $this->prepareVerificationData($payGateTranID);

        // step1: verify
        $this->verifyStep($client, $params);

        // step2: settle
        $this->settleStep($client, $params);

        return $this->createReceipt($payGateTranID);
    }

    /**
     * payment verification step
     *
     * @param $client
     * @param $params
     *
     * @throws InvalidPaymentException
     */
    protected function verifyStep($client, $params)
    {
        $result = $client->RequestVerification($params);
        if (!$result) {
            throw new InvalidPaymentException("خطای فراخوانی متد وريفای رخ داده است.");
        }

        $result = $result->RequestVerificationResult;
        if ($result != '500') {
            $message = "خطای شماره: ".$result." در هنگام Verify";
            throw  new InvalidPaymentException($message);
        }
    }

    /**
     * payment settlement step.
     *
     * @param $client
     * @param $params
     *
     * @throws InvalidPaymentException
     */
    protected function settleStep($client, $params)
    {
        $result = $client->RequestReconciliation($params);
        if (!$result) {
            throw new InvalidPaymentException('خطای فراخوانی متد تسويه رخ داده است.');
        }

        $result = $result->RequestReconciliationResult;
        if ($result != '600') {
            $message = "خطای شماره: ".$result." در هنگام Settlement";
            throw new InvalidPaymentException($message);
        }
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
        $receipt = new Receipt('asanpardakht', $referenceId);

        return $receipt;
    }

    /**
     * Prepare data for payment verification
     *
     * @param $payGateTranID
     *
     * @return array
     *
     * @throws \SoapFault
     */
    protected function prepareVerificationData($payGateTranID)
    {
        $credentials = array(
            $this->settings->username,
            $this->settings->password
        );

        $encryptedCredentials = $this->encrypt(implode(',', $credentials));

        return array(
            'merchantConfigurationID' => $this->settings->merchantId,
            'encryptedCredentials' => $encryptedCredentials,
            'payGateTranID' => $payGateTranID
        );
    }

    /**
     * Prepare data for purchasing invoice
     *
     * @return array
     *
     * @throws \SoapFault
     */
    protected function preparePurchaseData()
    {
        if (!empty($this->invoice->getDetails()['description'])) {
            $description = $this->invoice->getDetails()['description'];
        } else {
            $description = $this->settings->description;
        }

        // configs
        $username = $this->settings->username;
        $password = $this->settings->password;
        $callBackUrl = $this->settings->callbackUrl;

        // invoice details
        $price = $this->invoice->getAmount() * 10; // convert to rial
        $additionalData = $description ?? '';
        $orderId = crc32($this->invoice->getUuid());
        $localDate = date("Ymd His");

        // box and encrypt everything
        $requestString = "1,{$username},{$password},{$orderId},{$price},{$localDate},{$additionalData},{$callBackUrl},0";
        $encryptedRequestString = $this->encrypt($requestString);

        return array(
            'merchantConfigurationID' => $this->settings->merchantId,
            'encryptedRequest' => $encryptedRequestString
        );
    }

    /**
     * Encrypt given string.
     *
     * @param $string
     *
     * @return mixed
     *
     * @throws \SoapFault
     */
    protected function encrypt($string)
    {
        $client = $this->createSoapClient($this->settings->apiUtilsUrl);

        $params = array(
            'aesKey' => $this->settings->key,
            'aesVector' => $this->settings->iv,
            'toBeEncrypted' => $string
        );

        $result = $client->EncryptInAES($params);

        return $result->EncryptInAESResult;
    }

    /**
     * Decrypt given string.
     *
     * @param $string
     *
     * @return mixed
     *
     * @throws \SoapFault
     */
    protected function decrypt($string)
    {
        $client = $this->createSoapClient($this->settings->apiUtilsUrl);

        $params = array(
            'aesKey' => $this->settings->key,
            'aesVector' => $this->settings->iv,
            'toBeDecrypted' => $string
        );

        $result = $client->DecryptInAES($params);

        return $result->DecryptInAESResult;
    }

    /**
     * create a new SoapClient
     *
     * @param $url
     *
     * @return \SoapClient
     *
     * @throws \SoapFault
     */
    protected function createSoapClient($url)
    {
        $opts = array(
            'ssl' => array(
                'verify_peer'=>false,
                'verify_peer_name'=>false
            )
        );

        $configs = array('stream_context' => stream_context_create($opts));

        return new \SoapClient($url, $configs);
    }
}
