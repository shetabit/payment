<?php

namespace Shetabit\Payment\Drivers\Irankish;

use Shetabit\Payment\Abstracts\Driver;
use Shetabit\Payment\Exceptions\InvalidPaymentException;
use Shetabit\Payment\Exceptions\PurchaseFailedException;
use Shetabit\Payment\Contracts\ReceiptInterface;
use Shetabit\Payment\Invoice;
use Shetabit\Payment\Receipt;

class Irankish extends Driver
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
     * Irankish constructor.
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
        if (!empty($this->invoice->getDetails()['description'])) {
            $description = $this->invoice->getDetails()['description'];
        } else {
            $description = $this->settings->description;
        }

        $data = array(
            'amount' => $this->invoice->getAmount() * 10, // convert to rial
            'merchantId' => $this->settings->merchantId,
            'description' => $description,
            'revertURL' => $this->settings->callbackUrl,
            'invoiceNo' => crc32($this->invoice->getUuid()),
            'paymentId' => crc32($this->invoice->getUuid()),
            'specialPaymentId' => crc32($this->invoice->getUuid()),
        );

        $soap = new \SoapClient(
            $this->settings->apiPurchaseUrl
        );
        $response = $soap->MakeToken($data);

        if ($response->MakeTokenResult->result != false) {
            $this->invoice->transactionId($response->MakeTokenResult->token);
        } else {
            // error has happened
            $message = $response->MakeTokenResult->message ?? 'خطا در هنگام درخواست برای پرداخت رخ داده است.';
            throw new PurchaseFailedException($message);
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
        $payUrl = $this->settings->apiPaymentUrl;

        return $this->redirectWithForm(
            $payUrl,
            [
                'token' => $this->invoice->getTransactionId(),
                'merchantId' => $this->settings->merchantId,
            ],
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
        $data = array(
            'merchantId' => $this->settings->merchantId,
            'sha1Key' => $this->settings->sha1Key,
            'token' => $this->invoice->getTransactionId(),
            'amount' => $this->invoice->getAmount() * 10, // convert to rial
            'referenceNumber' => request()->get('referenceId'),
        );

        $soap = new \SoapClient($this->settings->apiVerificationUrl);
        $response = $soap->KicccPaymentsVerification($data);

        $status = (int) ($response->KicccPaymentsVerificationResult);

        if ($status != $data['amount']) {
            $this->notVerified($status);
        }

        return $this->createReceipt($data['referenceNumber']);
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
        $receipt = new Receipt('irankish', $referenceId);

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
        $translations = array(
            110 => 'دارنده کارت انصراف داده است',
            120 => 'موجودی حساب کافی نمی باشد',
            121 => 'مبلغ تراکنشهای کارت بیش از حد مجاز است',
            130 => 'اطلاعات کارت نادرست می باشد',
            131 => 'رمز کارت اشتباه است',
            132 => 'کارت مسدود است',
            133 => 'کارت منقضی شده است',
            140 => 'زمان مورد نظر به پایان رسیده است',
            150 => 'خطای داخلی بانک به وجود آمده است',
            160 => 'خطای انقضای کارت به وجود امده یا اطلاعات CVV2 اشتباه است',
            166 => 'بانک صادر کننده کارت شما مجوز انجام تراکنش را صادر نکرده است',
            167 => 'خطا در مبلغ تراکنش',
            200 => 'مبلغ تراکنش بیش از حدنصاب مجاز',
            201 => 'مبلغ تراکنش بیش از حدنصاب مجاز برای روز کاری',
            202 => 'مبلغ تراکنش بیش از حدنصاب مجاز برای ماه کاری',
            203 => 'تعداد تراکنشهای مجاز از حد نصاب گذشته است',
            499 => 'خطای سیستمی ، لطفا مجددا تالش فرمایید',
            500 => 'خطا در تایید تراکنش های خرد شده',
            501 => 'خطا در تایید تراکتش ، ویزگی تایید خودکار',
            502 => 'آدرس آی پی نا معتبر',
            503 => 'پذیرنده در حالت تستی می باشد ، مبلغ نمی تواند بیش از حد مجاز تایین شده برای پذیرنده تستی باشد',
            504 => 'خطا در بررسی الگوریتم شناسه پرداخت',
            505 => 'مدت زمان الزم برای انجام تراکنش تاییدیه به پایان رسیده است',
            506 => 'ذیرنده یافت نشد',
            507 => 'توکن نامعتبر/طول عمر توکن منقضی شده است',
            508 => 'توکن مورد نظر یافت نشد و یا منقضی شده است',
            509 => 'خطا در پارامترهای اجباری خرید تسهیم شده',
            510 => 'خطا در تعداد تسهیم | مبالغ کل تسهیم مغایر با مبلغ کل ارائه شده | خطای شماره ردیف تکراری',
            511 => 'حساب مسدود است',
            512 => 'حساب تعریف نشده است',
            513 => 'شماره تراکنش تکراری است',
            -20 => 'در درخواست کارکتر های غیر مجاز وجو دارد',
            -30 => 'تراکنش قبلا برگشت خورده است',
            -50 => 'طول رشته درخواست غیر مجاز است',
            -51 => 'در در خواست خطا وجود دارد',
            -80 => 'تراکنش مورد نظر یافت نشد',
            -81 => ' خطای داخلی بانک',
            -90 => 'تراکنش قبلا تایید شده است'
        );
        if (array_key_exists($status, $translations)) {
            throw new InvalidPaymentException($translations[$status]);
        } else {
            throw new InvalidPaymentException('خطای ناشناخته ای رخ داده است.');
        }
    }
}
