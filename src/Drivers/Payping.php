<?php

namespace Shetabit\Payment\Drivers;

use GuzzleHttp\Client;
use Shetabit\Payment\Abstracts\Driver;
use Shetabit\Payment\Exceptions\InvalidPaymentException;
use Shetabit\Payment\Invoice;

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
     * Zarinpal constructor.
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

        $response = $this->client->request(
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

        if (empty($body['code'])) {
            // an error has happened
        } else {
            $this->invoice->transactionId($body['code']);
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
     * @throws InvalidPaymentException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function verify()
    {
        $data = [
            'amount' => $this->invoice->getAmount(),
            'refId'  => $this->invoice->getTransactionId() ?? request()->input('refid'),
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
        $body = json_decode($response->getBody()->getContents(), true);

        if (!isset($body['Status']) || $body['Status'] != 100) {
            $this->notVerified($body['Status']);
        }
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
            "-1" => "اطلاعات ارسال شده ناقص است.",
            "-2" => "IP و يا مرچنت كد پذيرنده صحيح نيست",
            "-3" => "با توجه به محدوديت هاي شاپرك امكان پرداخت با رقم درخواست شده ميسر نمي باشد",
            "-4" => "سطح تاييد پذيرنده پايين تر از سطح نقره اي است.",
            "-11" => "درخواست مورد نظر يافت نشد.",
            "-12" => "امكان ويرايش درخواست ميسر نمي باشد.",
            "-21" => "هيچ نوع عمليات مالي براي اين تراكنش يافت نشد",
            "-22" => "تراكنش نا موفق ميباشد",
            "-33" => "رقم تراكنش با رقم پرداخت شده مطابقت ندارد",
            "-34" => "سقف تقسيم تراكنش از لحاظ تعداد يا رقم عبور نموده است",
            "-40" => "اجازه دسترسي به متد مربوطه وجود ندارد.",
            "-41" => "اطلاعات ارسال شده مربوط به AdditionalData غيرمعتبر ميباشد.",
            "-42" => "مدت زمان معتبر طول عمر شناسه پرداخت بايد بين 30 دقيه تا 45 روز مي باشد.",
            "-54" => "درخواست مورد نظر آرشيو شده است",
            "101" => "عمليات پرداخت موفق بوده و قبلا PaymentVerification تراكنش انجام شده است.",
        );
        if (array_key_exists($status, $translations)) {
            throw new InvalidPaymentException($translations[$status]);
        } else {
            throw new InvalidPaymentException('خطای ناشناخته ای رخ داده است.');
        }
    }
}
