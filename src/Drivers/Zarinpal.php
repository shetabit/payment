<?php

namespace Shetabit\Payment\Drivers;

use GuzzleHttp\Client;
use Shetabit\Payment\Abstracts\Driver;
use Shetabit\Payment\Exceptions\{InvalidPaymentException, PurchaseFailedException};
use Shetabit\Payment\{Invoice, Receipt};

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
    public function purchase() : string
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

        $client = new \SoapClient($this->getPurchaseUrl(), ['encoding' => 'UTF-8']);

        $result = $client->PaymentRequest($data);

        if ($result->Status != 100 || empty($body['Authority'])) {
            // some error has happened
            throw new PurchaseFailedException('خطا در هنگام درخواست برای پرداخت رخ داده است.');
        }

        $this->invoice->transactionId($result->Authority);

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
        $transactionId = $this->invoice->getTransactionId();
        $paymentUrl = $this->getPaymentUrl();

        if (strtolower($this->getMode()) == 'zaringate') {
            $payUrl = str_replace(':authority', $transactionId, $paymentUrl);
        } else {
            $payUrl = $paymentUrl . $transactionId;
        }

        // redirect using laravel logic
        return redirect()->to($payUrl);
    }

    /**
     * Verify payment
     *
     * @return mixed|void
     *
     * @throws InvalidPaymentException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function verify()
    {
        $authority = $this->invoice->getTransactionId() ?? request()->get('Authority');
        $status = request()->get('Status');

        $data = [
            'MerchantID' => $this->settings->merchantId,
            'Authority' => $authority,
            'Amount' => $this->invoice->getAmount(),
        ];

        if ($status != 'OK') {
            throw new InvalidPaymentException('عملیات پرداخت توسط کاربر لغو شد.');
        }

        $client = new SoapClient('https://zarinpal.com/pg/services/WebGate/wsdl', ['encoding' => 'UTF-8']);

        $result = $client->PaymentVerification($data);

        if ($result->Status != 100) {
            $this->notVerified($result->Status);
        }

        return $this->createReceipt($result->RefID);
    }

    /**
     * Generate the payment's receipt
     *
     * @param $referenceId
     *
     * @return Receipt
     */
    public function createReceipt($referenceId)
    {
        $receipt = new Receipt('zarinpal', $referenceId);

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

    /**
     * Retrieve purchase url
     *
     * @return string
     */
    protected function getPurchaseUrl() : string
    {
        $mode = $this->getMode();

        switch($mode) {
            case 'sandbox':
                $url = $this->settings->sandboxApiPurchaseUrl;
                break;
            case 'zaringate':
                $url = $this->settings->zaringateApiPurchaseUrl;
                break;
            default: // default: normal
                $url = $this->settings->apiPurchaseUrl;
                break;
        }

        return $url;
    }

    /**
     * Retrieve Payment url
     *
     * @return string
     */
    protected function getPaymentUrl() : string
    {
        $mode = $this->getMode();

        switch($mode) {
            case 'sandbox':
                $url = $this->settings->sandboxApiPaymentUrl;
                break;
            case 'zaringate':
                $url = $this->settings->zaringateApiPaymentUrl;
                break;
            default: // default: normal
                $url = $this->settings->apiPaymentUrl;
                break;
        }

        return $url;
    }

    /**
     * Retrieve verification url
     *
     * @return string
     */
    protected function getVerificationUrl() : string
    {
        $mode = $this->getMode();

        switch($mode) {
            case 'sandbox':
                $url = $this->settings->sandboxApiVerificationUrl;
                break;
            case 'zaringate':
                $url = $this->settings->zaringateApiVerificationUrl;
                break;
            default: // default: normal
                $url = $this->settings->apiVerificationUrl;
                break;
        }

        return $url;
    }

    /**
     * Retrieve payment mode.
     *
     * @return string
     */
    protected function getMode() : string
    {
        return strtolower($this->settings->mode);
    }
}
