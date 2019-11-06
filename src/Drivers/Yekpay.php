<?php

namespace Shetabit\Payment\Drivers;

use Shetabit\Payment\Abstracts\Driver;
use Shetabit\Payment\Exceptions\{InvalidPaymentException, PurchaseFailedException};
use Shetabit\Payment\{Invoice, Receipt};

class Yekpay extends Driver
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
     * Yekpay constructor.
     * Construct the class with the relevant settings.
     *
     * @param Invoice $invoice
     * @param $settings
     */
    public function __construct(Invoice $invoice, $settings)
    {
        $this->invoice($invoice);
        $this->settings = (object)$settings;
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
        $options = array('trace' => true);
        $client = new \SoapClient($this->settings->apiPurchaseUrl, $options);

        $data = new \stdClass();

        if (!empty($this->invoice->getDetails()['description'])) {
            $description = $this->invoice->getDetails()['description'];
        } else {
            $description = $this->settings->description;
        }

        $data->merchantId = $this->settings->merchantId;
        $data->amount = $this->invoice->getAmount();
        $data->callback = $this->settings->callbackUrl;
        $data->orderNumber = intval(1, time()) . crc32($this->invoice->getUuid());

        $data->fromCurrencyCode = 978;
        $data->toCurrencyCode = 364;

        $data->firstName = $this->extractDetails('firstName');
        $data->lastName = $this->extractDetails('lastName');
        $data->email = $this->extractDetails('email');
        $data->mobile = $this->extractDetails('mobile');

        $data->address = $this->extractDetails('address');
        $data->country = $this->extractDetails('country');
        $data->postalCode = $this->extractDetails('postalCode');
        $data->city = $this->extractDetails('city');

        $data->description = $description;

        $response = json_decode($client->request($data));

        if ($response->Code == 100) {
            $this->invoice->transactionId($response->Authority);
        } else {
            //"Request failed with Error code: $response->Code and Error message: $response->Description";
            throw new PurchaseFailedException($response->Description);
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
     * Verify payment
     *
     * @return mixed|void
     * @throws InvalidPaymentException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function verify()
    {
        $options = array('trace' => true);
        $client = new SoapClient($this->settings->apiVerificationUrl, $options);

        $data = new \stdClass();

        $data->merchantId = $this->settings->merchantId;
        $data->authority = $this->invoice->getTransactionId() ?? request()->input('authority');

        $response = json_decode($client->verify($data));

        if ($response->Code != 100) {
            $this->notVerified($transaction->message ?? 'payment failed');
        } else {
            //"Success Payment with reference: $response->Reference and message: $transaction->message";
        }
    }

    /**
     * Trigger an exception
     *
     * @param $message
     * @throws InvalidPaymentException
     */
    private function notVerified($message)
    {
        if ($message) {
            throw new InvalidPaymentException($message);
        } else {
            throw new InvalidPaymentException('payment failed');
        }
    }
}
