<?php

namespace Shetabit\Payment\Drivers;

use GuzzleHttp\Client;
use Shetabit\Payment\Abstracts\Driver;
use Shetabit\Payment\Exceptions\InvalidPaymentException;
use Shetabit\Payment\Invoice;

class Zarinpal extends Driver
{
    /**
     * Zarinpal Client.
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
        $this->settings = (object)$settings;
        $this->client = new Client();
    }

    /**
     * Purchase Invoice.
     *
     * @return string
     */
    public function purchase()
    {
        if (!empty($this->invoice->getDetails()['description'])) {
            $description = $this->invoice->getDetails()['description'];
        } else {
            $description = $this->settings->description;
        }

        $data = array(
            'MerchantID' => $this->settings->merchantId,
            'Amount' => $this->invoice->getAmount(),
            'CallbackURL' => $this->settings->callbackUrl,
            'Description' => $description,
            'AdditionalData' => $this->invoice->getDetails()
        );

        $response = $this->client->request(
            'POST',
            $this->settings->apiPurchaseUrl,
            ["json" => $data]
        );
        $body = json_decode($response->getBody()->getContents(), true);

        if (empty($body['Authority'])) {
            $body['Authority'] = null;
        } else {
            $this->invoice->transactionId($body['Authority']);
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
        $payUrl = $this->settings->apiPaymentUrl . $this->invoice->getTransactionId();

        // redirect using laravel logic
        return redirect()->to($payUrl);
    }

    /**
     * get payment url
     *
     * @return String|url
     */
    public function getPayUrl()
    {
        return $this->settings->apiPaymentUrl . $this->invoice->getTransactionId();
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
            'MerchantID' => $this->settings->merchantId,
            'Authority' => $this->invoice->getTransactionId(),
            'Amount' => $this->invoice->getAmount(),
        ];

        $response = $this->client->request(
            'POST',
            $this->settings->apiVerificationUrl,
            ['json' => $data]
        );
        $body = json_decode($response->getBody()->getContents(), true);

        $this->invoice->refId($body['RefID']);

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
