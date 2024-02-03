<p align="center"><img src="resources/images/payment.png?raw=true"></p>



# Laravel Payment Gateway



[![Software License][ico-license]](LICENSE.md)
[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads on Packagist][ico-download]][link-packagist]
[![StyleCI](https://github.styleci.io/repos/169948762/shield?branch=master)](https://github.styleci.io/repos/169948762)
[![Maintainability](https://api.codeclimate.com/v1/badges/e6a80b17298cb4fcb56d/maintainability)](https://codeclimate.com/github/shetabit/payment/maintainability)
[![Quality Score][ico-code-quality]][link-code-quality]

This is a Laravel Package for Payment Gateway Integration. This package supports `Laravel 5.8+`.

[Donate me](https://yekpay.me/mahdikhanzadi) if you like this package :sunglasses: :bowtie:

For PHP integration you can use [shetabit/multipay](https://github.com/shetabit/multipay) package.

> This packages works with multiple drivers, and you can create custom drivers if you can't find them in the [current drivers list](#list-of-available-drivers) (below list).

- [داکیومنت فارسی][link-fa]
- [English documents][link-en]
- [中文文档][link-zh]

# List of contents

- [Laravel Payment Gateway](#laravel-payment-gateway)
- [List of contents](#list-of-contents)
- [List of available drivers](#list-of-available-drivers)
  - [Install](#install)
  - [Configure](#configure)
  - [How to use](#how-to-use)
      - [Working with invoices](#working-with-invoices)
      - [Purchase invoice](#purchase-invoice)
      - [Pay invoice](#pay-invoice)
      - [Verify payment](#verify-payment)
      - [Useful methods](#useful-methods)
      - [Create custom drivers:](#create-custom-drivers)
      - [Events](#events)
  - [Change log](#change-log)
  - [Contributing](#contributing)
  - [Security](#security)
  - [Credits](#credits)
  - [License](#license)

# List of available drivers

- [asanpardakht](https://asanpardakht.ir/) :heavy_check_mark:
- [aqayepardakht](https://aqayepardakht.ir/) :heavy_check_mark:
- [atipay](https://www.atipay.net/) :heavy_check_mark:
- [azkiVam (Installment payment)](https://www.azkivam.com/) :heavy_check_mark:
- [behpardakht (mellat)](http://www.behpardakht.com/) :heavy_check_mark:
- [bitpay](https://bitpay.ir/) :heavy_check_mark:
- [digipay](https://www.mydigipay.com/) :heavy_check_mark:
- [etebarino (Installment payment)](https://etebarino.com/) :heavy_check_mark:
- [fanavacard](https://www.fanava.com/) :heavy_check_mark:
- [idpay](https://idpay.ir/) :heavy_check_mark:
- [irankish](http://irankish.com/) :heavy_check_mark:
- [local](#local-driver) :heavy_check_mark:
- [jibit](https://jibit.ir/) :heavy_check_mark:
- [nextpay](https://nextpay.ir/) :heavy_check_mark:
- [omidpay](https://omidpayment.ir/) :heavy_check_mark:
- [parsian](https://www.pec.ir/) :heavy_check_mark:
- [pasargad](https://bpi.ir/) :heavy_check_mark:
- [payir](https://pay.ir/) :heavy_check_mark:
- [payfa](https://payfa.com/) :heavy_check_mark:
- [paypal](http://www.paypal.com/) (will be added soon in next version)
- [payping](https://www.payping.ir/) :heavy_check_mark:
- [paystar](http://paystar.ir/) :heavy_check_mark:
- [poolam](https://poolam.ir/) :heavy_check_mark:
- [rayanpay](https://rayanpay.com/) :heavy_check_mark:
- [sadad (melli)](https://sadadpsp.ir/) :heavy_check_mark:
- [saman](https://www.sep.ir) :heavy_check_mark:
- [sep (saman electronic payment) Keshavarzi & Saderat](https://www.sep.ir) :heavy_check_mark:
- [sepehr (saderat)](https://www.sepehrpay.com/) :heavy_check_mark:
- [sepordeh](https://sepordeh.com/) :heavy_check_mark:
- [sizpay](https://www.sizpay.ir/) :heavy_check_mark:
- [toman](https://tomanpay.net/) :heavy_check_mark:
- [vandar](https://vandar.io/) :heavy_check_mark:
- [walleta (Installment payment)](https://walleta.ir/) :heavy_check_mark:
- [yekpay](https://yekpay.com/) :heavy_check_mark:
- [zarinpal](https://www.zarinpal.com/) :heavy_check_mark:
- [zibal](https://www.zibal.ir/) :heavy_check_mark:
- Others are under way.

**Help me to add the gateways below by creating `pull requests`**

- stripe
- authorize
- 2checkout
- braintree
- skrill
- payU
- amazon payments
- wepay
- payoneer
- paysimple

> you can create your own custom drivers if it does not exist in the list, read the `Create custom drivers` section.

## Install

Via Composer

``` bash
$ composer require shetabit/payment
```

## Publish Vendor Files

- **publish configuration files:**
``` bash
php artisan vendor:publish --tag=payment-config
```

 - **publish views for customization:**
``` bash
php artisan vendor:publish --tag=payment-views
```

## Configure

If you are using `Laravel 5.5` or higher then you don't need to add the provider and alias. (Skip to b)

a. In your `config/app.php` file add these two lines.

```php
// In your providers array.
'providers' => [
    ...
    Shetabit\Payment\Provider\PaymentServiceProvider::class,
],

// In your aliases array.
'aliases' => [
    ...
    'Payment' => Shetabit\Payment\Facade\Payment::class,
],
```

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
        // Fill in the credentials here.
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

your `Invoice` holds your payment details, so initially we'll talk about `Invoice` class. 

#### Working with invoices

before doing any thing you need to use `Invoice` class to create an invoice.


In your code, use it like the below:

```php
// At the top of the file.
use Shetabit\Multipay\Invoice;
...

// Create new invoice.
$invoice = new Invoice;

// Set invoice amount.
$invoice->amount(1000);

// Add invoice details: There are 4 syntax available for this.
// 1
$invoice->detail(['detailName' => 'your detail goes here']);
// 2 
$invoice->detail('detailName','your detail goes here');
// 3
$invoice->detail(['name1' => 'detail1','name2' => 'detail2']);
// 4
$invoice->detail('detailName1','your detail1 goes here')
        ->detail('detailName2','your detail2 goes here');

```
available methods:

- `uuid`: set the invoice unique id
- `getUuid`: retrieve the invoice current unique id
- `detail`: attach some custom details into invoice
- `getDetails`: retrieve all custom details 
- `amount`: set the invoice amount
- `getAmount`: retrieve invoice amount
- `transactionId`: set invoice payment transaction id
- `getTransactionId`: retrieve payment transaction id
- `via`: set a driver we use to pay the invoice
- `getDriver`: retrieve the driver

#### Purchase invoice
In order to pay the invoice, we need the payment transactionId.
We purchase the invoice to retrieve transaction id:

```php
// At the top of the file.
use Shetabit\Multipay\Invoice;
use Shetabit\Payment\Facade\Payment;
...

// Create new invoice.
$invoice = (new Invoice)->amount(1000);

// Purchase the given invoice.
Payment::purchase($invoice,function($driver, $transactionId) {
	// We can store $transactionId in database.
});

// Purchase method accepts a callback function.
Payment::purchase($invoice, function($driver, $transactionId) {
    // We can store $transactionId in database.
});

// You can specify callbackUrl
Payment::callbackUrl('http://yoursite.com/verify')->purchase(
    $invoice, 
    function($driver, $transactionId) {
    	// We can store $transactionId in database.
	}
);
```

#### Pay invoice

After purchasing the invoice, we can redirect the user to the bank payment page:

```php
// At the top of the file.
use Shetabit\Multipay\Invoice;
use Shetabit\Payment\Facade\Payment;
...

// Create new invoice.
$invoice = (new Invoice)->amount(1000);
// Purchase and pay the given invoice.
// You should use return statement to redirect user to the bank page.
return Payment::purchase($invoice, function($driver, $transactionId) {
    // Store transactionId in database as we need it to verify payment in the future.
})->pay()->render();

// Do all things together in a single line.
return Payment::purchase(
    (new Invoice)->amount(1000), 
    function($driver, $transactionId) {
    	// Store transactionId in database.
        // We need the transactionId to verify payment in the future.
	}
)->pay()->render();

// Retrieve json format of Redirection (in this case you can handle redirection to bank gateway)
return Payment::purchase(
    (new Invoice)->amount(1000), 
    function($driver, $transactionId) {
    	// Store transactionId in database.
        // We need the transactionId to verify payment in the future.
	}
)->pay()->toJson();
```

#### Verify payment

When user has completed the payment, the bank redirects them to your website, then you need to **verify your payment** in order to ensure the `invoice` has been **paid**.

```php
// At the top of the file.
use Shetabit\Payment\Facade\Payment;
use Shetabit\Multipay\Exceptions\InvalidPaymentException;
...

// You need to verify the payment to ensure the invoice has been paid successfully.
// We use transaction id to verify payments
// It is a good practice to add invoice amount as well.
try {
	$receipt = Payment::amount(1000)->transactionId($transaction_id)->verify();

    // You can show payment referenceId to the user.
    echo $receipt->getReferenceId();

    ...
} catch (InvalidPaymentException $exception) {
    /**
    	when payment is not verified, it will throw an exception.
    	We can catch the exception to handle invalid payments.
    	getMessage method, returns a suitable message that can be used in user interface.
    **/
    echo $exception->getMessage();
}
```

#### Useful methods

- ###### `callbackUrl`: can be used to change callbackUrl on the runtime.

  ```php
  // At the top of the file.
  use Shetabit\Multipay\Invoice;
  use Shetabit\Payment\Facade\Payment;
  ...
  
  // Create new invoice.
  $invoice = (new Invoice)->amount(1000);
  
  // Purchase the given invoice.
  Payment::callbackUrl($url)->purchase(
      $invoice, 
      function($driver, $transactionId) {
      // We can store $transactionId in database.
  	}
  );
  ```

- ###### `amount`: you can set the invoice amount directly

  ```php
  // At the top of the file.
  use Shetabit\Multipay\Invoice;
  use Shetabit\Payment\Facade\Payment;
  ...
  
  // Purchase (we set invoice to null).
  Payment::callbackUrl($url)->amount(1000)->purchase(
      null, 
      function($driver, $transactionId) {
      // We can store $transactionId in database.
  	}
  );
  ```

- ###### `via`: change driver on the fly

  ```php
  // At the top of the file.
  use Shetabit\Multipay\Invoice;
  use Shetabit\Payment\Facade\Payment;
  ...
  
  // Create new invoice.
  $invoice = (new Invoice)->amount(1000);
  
  // Purchase the given invoice.
  Payment::via('driverName')->purchase(
      $invoice, 
      function($driver, $transactionId) {
      // We can store $transactionId in database.
  	}
  );
  ```
  
- ###### `config`: set driver configs on the fly

  ```php
  // At the top of the file.
  use Shetabit\Multipay\Invoice;
  use Shetabit\Payment\Facade\Payment;
  ...
  
  // Create new invoice.
  $invoice = (new Invoice)->amount(1000);
  
  // Purchase the given invoice with custom driver configs.
  Payment::config('mechandId', 'your mechand id')->purchase(
      $invoice,
      function($driver, $transactionId) {
      // We can store $transactionId in database.
  	}
  );

  // Also we can change multiple configs at the same time.
  Payment::config(['key1' => 'value1', 'key2' => 'value2'])->purchase(
      $invoice,
      function($driver, $transactionId) {
      // We can store $transactionId in database.
  	}
  );
  ```

#### Create custom drivers:

First you have to add the name of your driver, in the drivers array and also you can specify any config parameters you want.

```php
'drivers' => [
    'zarinpal' => [...],
    'my_driver' => [
        ... // Your Config Params here.
    ]
]
```

Now you have to create a Driver Map Class that will be used to pay invoices.
In your driver, You just have to extend `Shetabit\Payment\Abstracts\Driver`.

Eg. You created a class: `App\Packages\PaymentDriver\MyDriver`.

```php
namespace App\Packages\PaymentDriver;

use Shetabit\Multipay\Abstracts\Driver;
use Shetabit\Multipay\Exceptions\InvalidPaymentException;
use Shetabit\Multipay\{Contracts\ReceiptInterface, Invoice, Receipt};

class MyDriver extends Driver
{
    protected $invoice; // Invoice.

    protected $settings; // Driver settings.

    public function __construct(Invoice $invoice, $settings)
    {
        $this->invoice($invoice); // Set the invoice.
        $this->settings = (object) $settings; // Set settings.
    }

    // Purchase the invoice, save its transactionId and finaly return it.
    public function purchase() {
        // Request for a payment transaction id.
        ...
            
        $this->invoice->transactionId($transId);
        
        return $transId;
    }
    
    // Redirect into bank using transactionId, to complete the payment.
    public function pay() {
        // It is better to set bankApiUrl in config/payment.php and retrieve it here:
        $bankUrl = $this->settings->bankApiUrl; // bankApiUrl is the config name.

        // Prepare payment url.
        $payUrl = $bankUrl.$this->invoice->getTransactionId();

        // Redirect to the bank.
        return redirect()->to($payUrl);
    }
    
    // Verify the payment (we must verify to ensure that user has paid the invoice).
    public function verify(): ReceiptInterface {
        $verifyPayment = $this->settings->verifyApiUrl;
        
        $verifyUrl = $verifyPayment.$this->invoice->getTransactionId();
        
        ...
        
        /**
			Then we send a request to $verifyUrl and if payment is not valid we throw an InvalidPaymentException with a suitable message.
        **/
        throw new InvalidPaymentException('a suitable message');
        
        /**
        	We create a receipt for this payment if everything goes normally.
        **/
        return new Receipt('driverName', 'payment_receipt_number');
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

#### Events

You can listen for 2 events

- **InvoicePurchasedEvent**: Occurs when an invoice is purchased (after purchasing invoice is done successfully).
- **InvoiceVerifiedEvent**: Occurs when an invoice is verified successfully.

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has been changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CONDUCT](CONDUCT.md) for details.

## Security

If you discover any security related issues, please email khanzadimahdi@gmail.com instead of using the issue tracker.

## Credits

- [Mahdi khanzadi][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/shetabit/payment.svg?style=flat-square
[ico-download]: https://img.shields.io/packagist/dt/shetabit/payment.svg?color=%23F18&style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/shetabit/payment.svg?label=Code%20Quality&style=flat-square

[link-fa]: README-FA.md
[link-en]: README.md
[link-zh]: README-ZH.md
[link-packagist]: https://packagist.org/packages/shetabit/payment
[link-code-quality]: https://scrutinizer-ci.com/g/shetabit/payment
[link-author]: https://github.com/khanzadimahdi
[link-contributors]: ../../contributors

## Star History

[![Star History Chart](https://api.star-history.com/svg?repos=shetabit/payment&type=Date)](https://star-history.com/#shetabit/payment&Date)
