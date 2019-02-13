# Laravel Payment Gateway

[![Software License][ico-license]](LICENSE.md)
[![Latest Version on Packagist][ico-version]][link-packagist]
[![StyleCI](https://github.styleci.io/repos/169948762/shield?branch=master)](https://github.styleci.io/repos/169948762)
[![Maintainability](https://api.codeclimate.com/v1/badges/e6a80b17298cb4fcb56d/maintainability)](https://codeclimate.com/github/shetabit/payment/maintainability)
[![Quality Score][ico-code-quality]][link-code-quality]

This is a Laravel Package for Payment Gateway Integration. This package supports `Laravel 5.2+`.

> This packages works with multiple drivers, and you can create custom drivers if there are not available in the current drivers list (below list).

List of available gateways:
- [zarinpal](https://www.zarinpal.com)
- Others are under way.

## Install

Via Composer

``` bash
$ composer require shetabit/payment
```


## Configure

If you are using `Laravel 5.5` or higher then you don't need to add the provider and alias.

In your `config/app.php` file add these two lines.

```php
# In your providers array.
'providers' => [
    ...
    Shetabit\payment\Provider\PaymentServiceProvider::class,
],

# In your aliases array.
'aliases' => [
    ...
    'Payment' => Shetabit\Payment\Facade\Payment::class,
],
```

then run `php artisan vendor:publish` to publish `config/payment.php` file in your config directory.

In the config file you can set the `default driver` to use for all your payments. But you can also change the driver at runtime.

Choose what gateway you would like to use in your application. Then make that as default driver so that you don't have to specify that everywhere. But, you can also use multiple gateways in a project.

```php
// Eg. if you want to use zarinpal.
'default' => 'zarinpal',
```

Then fill the credentials for that gateway in the drivers array.

```php
'drivers' => [
    'zarinpal' => [
        // Fill all the credentials here.
        'apiPurchaseUrl' => 'https://www.zarinpal.com/pg/rest/WebGate/PaymentRequest.json',
        'apiPaymentUrl' => 'https://www.zarinpal.com/pg/StartPay/',
        'apiVerificationUrl' => 'https://www.zarinpal.com/pg/rest/WebGate/PaymentVerification.json',
        'merchantId' => '',
        'callbackUrl' => 'http://yoursite.com/path/to',
        'description' => 'payment in '.config('app.name'),
    ],
    ...
]
```


## How to use

In your code, use it like the below:

```php
# On the top of the file.
use Shetabit\Payment\Facade\Payment;
Shetabit\Payment\InvoiceBuilder;
...

# create new invoice
$invoice = (new Invoice)->amount(1000);
# purchase and pay the given invoice
return Payment::purchase($invoice)->pay();

# after the payment, you need to verify it
$verificationResult = Payment::transactionId($transaction_id)->verify();
```
#### How to create a custom driver:

First you have to add the name of your driver, in the drivers array and also you can specify any config params you want.

```php
'drivers' => [
    'zarinpal' => [...],
    'my_driver' => [
        ... # Your Config Params here.
    ]
]
```

Now you have to create a Driver Map Class that will be used to send the SMS.
In your driver, You just have to extend `Tzsk\Sms\Abstracts\Driver`.

Ex. You created a class : `App\Packages\SMSDriver\MyDriver`.

```php
namespace App\Packages\PaymentDriver;

use Shetabit\Payment\Abstracts\Driver;

class MyDriver extends Driver
{
    protected $invoice; // invoice

    protected $settings; // driver settings

    public function __construct(Invoice $invoice, $settings)
    {
        $this->setInvoice($invoice); // set the invoice
        $this->settings = (object) $settings; // set settings
    }

    // purchase the invoice and finaly save its transactionId
    public function purchase() {
        ...
           
        $this->invoice->transactionId($body['Authority']);
    }
    
    // redirect into bank using transactionId, to complete the payment
    public function pay() {
        // its better to set bankApiUrl in config/payment.php and retrieve it here:
        $bankUrl = $this->settings->bankApiUrl; // bankApiUrl is the config name.

        //prepare payment url
        $payUrl = $bankUrl.$this->invoice->getTransactionId();

        // redirect to the bank
        return redirect()->to($payUrl);
    }
    
    // verify the payment (we must verify to insure that user has paid the invoice)
    public function verify() {
        $verifyPayment = $this->settings->verifyApiUrl;
        
        $verifyUrl = $verifyPayment.$this->invoice->getTransactionId();
        
        // then we send a request to $verifyUrl and return the result
        ...
    }
}
```

Once you create that class you have to specify it in the `payment.php` config file `map` section.

```php
'map' => [
    ...
    'my_driver' => App\Packages\PaymentDriver\MyDriver::class,
]
```

**Note:-** You have to make sure that the key of the `map` array is identical to the key of the `drivers` array.

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CONDUCT](CONDUCT.md) for details.

## Security

If you discover any security related issues, please email mailtokmahmed@gmail.com instead of using the issue tracker.

## Credits

- [Mahdi khanzadi][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/shetabit/payment.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/shetabit/payment.svg?label=Code%20Quality&style=flat-square

[link-packagist]: https://packagist.org/packages/shetabit/payment
[link-code-quality]: https://scrutinizer-ci.com/g/shetabit/payment
[link-author]: https://github.com/khanzadimahdi
[link-contributors]: ../../contributors
