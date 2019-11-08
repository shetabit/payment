<?php

namespace Shetabit\Payment\Drivers\Parsian;

use Shetabit\Payment\Abstracts\Driver;
use Shetabit\Payment\Exceptions\InvalidPaymentException;
use Shetabit\Payment\Exceptions\PurchaseFailedException;
use Shetabit\Payment\Contracts\ReceiptInterface;
use Shetabit\Payment\Invoice;
use Shetabit\Payment\Receipt;

class Parsian extends Driver
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
     * Parsian constructor.
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
        $soap = new \SoapClient($this->settings->apiPurchaseUrl);
        $response = $soap->SalePaymentRequest(
            ['requestData' => $this->preparePurchaseData()]
        );

        // no response from bank
        if (empty($response->SalePaymentRequestResult)) {
            throw new PurchaseFailedException('bank gateway not response');
        }

        $result = $response->SalePaymentRequestResult;

        if (isset($result->Status) && $result->Status == 0 && !empty($result->Token)) {
            $this->invoice->transactionId($result->Token);
        } else {
            // an error has happened
            throw new PurchaseFailedException($result->Message);
        }

        // return the transaction's id
        return $this->invoice->getTransactionId();
    }

    /**
     * Pay the Invoice
     *
     * @return mixed|string
     */
    public function pay()
    {
        $payUrl = $this->settings->apiPaymentUrl;

        return $this->redirectWithForm(
            $payUrl,
            ['Token' => $this->invoice->getTransactionId()],
            'POST'
        );
    }

    /**
     * Verify payment
     *
     * @return ReceiptInterface
     *
     * @throws InvalidPaymentException
     * @throws \SoapFault
     */
    public function verify() : ReceiptInterface
    {
        $status = request()->get('status');
        $token = request()->get('Token');

        if ($status != 0 || empty($token)) {
            throw new InvalidPaymentException('تراکنش توسط کاربر کنسل شده است.');
        }

        $data = $this->prepareVerificationData();
        $soap = new \SoapClient($this->settings->apiVerificationUrl);

        $response = $soap->ConfirmPayment(['requestData' => $data]);
        if (empty($response->ConfirmPaymentResult)) {
            throw new InvalidPaymentException('از سمت بانک پاسخی دریافت نشد.');
        }
        $result = $response->ConfirmPaymentResult;

        $hasWrongStatus = (!isset($result->Status) || $result->Status != 0);
        $hasWrongRRN = (!isset($result->RRN) || $result->RRN <= 0);
        if ($hasWrongStatus || $hasWrongRRN) {
            $message = 'خطا از سمت بانک با کد '.$result->Status.' رخ داده است.';
            throw new InvalidPaymentException($message);
        }

        return $this->createReceipt($result->RRN);
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
        $receipt = new Receipt('parsian', $referenceId);

        return $receipt;
    }

    /**
     * Prepare data for payment verification
     *
     * @return array
     */
    protected function prepareVerificationData()
    {
        $transactionId = $this->invoice->getTransactionId() ?? request()->get('Token');

        return array(
            'LoginAccount' 		=> $this->settings->merchantId,
            'Token' 		=> $transactionId,
        );
    }

    /**
     * Prepare data for purchasing invoice
     *
     * @return array
     */
    protected function preparePurchaseData()
    {
        if (!empty($this->invoice->getDetails()['description'])) {
            $description = $this->invoice->getDetails()['description'];
        } else {
            $description = $this->settings->description;
        }

        return array(
            'LoginAccount' 		=> $this->settings->merchantId,
            'Amount' 			=> $this->invoice->getAmount() * 10, // convert to rial
            'OrderId' 			=> crc32($this->invoice->getUuid()),
            'CallBackUrl' 		=> $this->settings->callbackUrl,
            'AdditionalData' 	=> $description,
        );
    }
}
