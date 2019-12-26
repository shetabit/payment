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

> This packages works with multiple drivers, and you can create custom drivers if there are not available in the [current drivers list](#list-of-available-drivers) (below list).

- [داکیومنت فارسی][link-fa]
- [english documents][link-en]

# List of contents

- [Available drivers](#list-of-available-drivers)
- [Install](#install)
- [Configure](#configure)
- [How to use](#how-to-use)
  - [Working with invoices](#working-with-invoices)
  - [Purchase invoice](#purchase-invoice)
  - [Pay invoice](#pay-invoice)
  - [Verify payment](#verify-payment)
  - [Useful methods](#useful-methods)  
  	- [Set callbackUrl on the fly  (runtime)](#callbackurl--can-be-used-to-change-callbackurl-on-the-runtime)
  	- [Set payment's amount (invoice amount)](#amount-you-can-set-the-invoice-amount-directly)
  	- [Change driver's on the fly (runtime)](#via--change-driver-on-the-fly)
  	- [Change driver's confings on the fly (runtime)](#config--set-driver-configs-on-the-fly)  
  - [Create custom drivers](#create-custom-drivers)
  - [Events](#events)
- [Change log](#change-log)
- [Contributing](#contributing)
- [Security](#security)
- [Credits](#credits)
- [License](#license)

# List of available drivers
- [asanpardakht](https://asanpardakht.ir/) :warning: (not tested)
- [behpardakht (mellat)](http://www.behpardakht.com/) :warning: (not tested)
- [idpay](https://idpay.ir/) :heavy_check_mark:
- [irankish](http://irankish.com/) :heavy_check_mark:
- [nextpay](https://nextpay.ir/) :heavy_check_mark:
- [parsian](https://www.pec.ir/) :heavy_check_mark:
- [payir](https://pay.ir/) :heavy_check_mark:
- [payping](https://www.payping.ir/) :heavy_check_mark:
- [paystar](http://paystar.ir/) :heavy_check_mark:
- [poolam](https://poolam.ir/) :heavy_check_mark:
- [sadad (melli)](https://sadadpsp.ir/) :heavy_check_mark:
- [saman](https://www.sep.ir) :heavy_check_mark:
- [yekpay](https://yekpay.com/) :heavy_check_mark:
- [zarinpal](https://www.zarinpal.com/) :heavy_check_mark:
- [zibal](https://www.zibal.ir/) :heavy_check_mark:
- Others are under way.

**Help me to add below gateways by creating `pull requests`**

- stripe
- paypal
- authorize
- 2checkout
- braintree
- skrill
- payU
- amazon payments
- wepay
- payoneer
- paysimple
- saderat
- pasargad

> you can create your own custom driver if not  exists in the list , read the `Create custom drivers` section.

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
    Shetabit\Payment\Provider\PaymentServiceProvider::class,
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

your `Invoice` holds your payment details, so we talk about `Invoice` class at first. 

#### Working with invoices

before doing any thing you need to use `Invoice` class to create an invoice.


In your code, use it like the below:

```php
# On the top of the file.
use Shetabit\Payment\Invoice;
...

# create new invoice
$invoice = new Invoice;

# set invoice amount
$invoice->amount(1000);

# add invoice details : we have 4 syntax
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

- `uuid` : set the invoice's unique id
- `getUuid` : retriev the invoice's current unique id
- `detail` : attach some custom details into invoice
- `getDetails` : retrieve all custom details 
- `amount` : set the invoice's amount
- `getAmount` : retrieve invoice's amount
- `transactionId` : set invoice's payment transaction id
- `getTransactionId` : retrieve payment's transaction id
- `via` : set a driver we use to pay the invoice
- `getDriver` : retrieve the driver

#### Purchase invoice
in order to pay the invoice, we need the payment's transactionId.
we purcahse the invoice to retrieve transaction id:

```php
# On the top of the file.
use Shetabit\Payment\Invoice;
use Shetabit\Payment\Facade\Payment;
...

# create new invoice
$invoice = (new Invoice)->amount(1000);

# purchase the given invoice
Payment::purchase($invoice,function($driver, $transactionId) {
	// we can store $transactionId in database
});

# purchase method accepts a callback function
Payment::purchase($invoice, function($driver, $transactionId) {
    // we can store $transactionId in database
});

# you can specify callbackUrl
Payment::callbackUrl('http://yoursite.com/verify')->purchase(
    $invoice, 
    function($driver, $transactionId) {
    	// we can store $transactionId in database
	}
);
```

#### Pay invoice

after purchasing the invoice, we can redirect user to the bank's payment page:

```php
# On the top of the file.
use Shetabit\Payment\Invoice;
use Shetabit\Payment\Facade\Payment;
...

# create new invoice
$invoice = (new Invoice)->amount(1000);
# purchase and pay the given invoice
// you should use return statement to redirect user to the bank's page.
return Payment::purchase($invoice, function($driver, $transactionId) {
    // store transactionId in database, we need it to verify payment in future.
})->pay();

# do all things together a single line
return Payment::purchase(
    (new Invoice)->amount(1000), 
    function($driver, $transactionId) {
    	// store transactionId in database.
        // we need the transactionId to verify payment in future
	}
)->pay();
```

#### Verify payment

when user completes the payment, the bank redirects him/her to your website, then you need to **verify your payment** in order to insure the `invoice` has been **paid**.

```php
# On the top of the file.
use Shetabit\Payment\Facade\Payment;
use Shetabit\Payment\Exceptions\InvalidPaymentException;
...

# you need to verify the payment to insure the invoice has been paid successfully
// we use transaction's id to verify payments
// its a good practice to add invoice's amount.
try {
	$receipt = Payment::amount(1000)->transactionId($transaction_id)->verify();

    // you can show payment's referenceId to user
    echo $receipt->getReferenceId();

    ...
} catch (InvalidPaymentException $exception) {
    /**
    	when payment is not verified , it throw an exception.
    	we can catch the excetion to handle invalid payments.
    	getMessage method, returns a suitable message that can be used in user interface.
    **/
    echo $exception->getMessage();
}
```

#### Useful methods

- ###### `callbackUrl` : can be used to change callbackUrl on the runtime.

  ```php
  # On the top of the file.
  use Shetabit\Payment\Invoice;
  use Shetabit\Payment\Facade\Payment;
  ...
  
  # create new invoice
  $invoice = (new Invoice)->amount(1000);
  
  # purchase the given invoice
  Payment::callbackUrl($url)->purchase(
      $invoice, 
      function($driver, $transactionId) {
      // we can store $transactionId in database
  	}
  );
  ```

- ###### `amount`: you can set the invoice amount directly

  ```php
  # On the top of the file.
  use Shetabit\Payment\Invoice;
  use Shetabit\Payment\Facade\Payment;
  ...
  
  # purchase (we set invoice to null)
  Payment::callbackUrl($url)->amount(1000)->purchase(
      null, 
      function($driver, $transactionId) {
      // we can store $transactionId in database
  	}
  );
  ```

- ###### `via` : change driver on the fly

  ```php
  # On the top of the file.
  use Shetabit\Payment\Invoice;
  use Shetabit\Payment\Facade\Payment;
  ...
  
  # create new invoice
  $invoice = (new Invoice)->amount(1000);
  
  # purchase the given invoice
  Payment::via('driverName')->purchase(
      $invoice, 
      function($driver, $transactionId) {
      // we can store $transactionId in database
  	}
  );
  ```
  
- ###### `config` : set driver configs on the fly

  ```php
  # On the top of the file.
  use Shetabit\Payment\Invoice;
  use Shetabit\Payment\Facade\Payment;
  ...
  
  # create new invoice
  $invoice = (new Invoice)->amount(1000);
  
  # purchase the given invoice with custom driver configs
  Payment::config('mechandId', 'your mechand id')->purchase(
      $invoice,
      function($driver, $transactionId) {
      // we can store $transactionId in database
  	}
  );

  # and we can also change multiple configs together
  Payment::config(['key1' => 'value1', 'key2' => 'value2'])->purchase(
      $invoice,
      function($driver, $transactionId) {
      // we can store $transactionId in database
  	}
  );
  ```

#### Create custom drivers:

First you have to add the name of your driver, in the drivers array and also you can specify any config parameters you want.

```php
'drivers' => [
    'zarinpal' => [...],
    'my_driver' => [
        ... # Your Config Params here.
    ]
]
```

Now you have to create a Driver Map Class that will be used to pay invoices.
In your driver, You just have to extend `Shetabit\Payment\Abstracts\Driver`.

Ex. You created a class : `App\Packages\PaymentDriver\MyDriver`.

```php
namespace App\Packages\PaymentDriver;

use Shetabit\Payment\Abstracts\Driver;
use Shetabit\Payment\Exceptions\InvalidPaymentException;
use Shetabit\Payment\{Contracts\ReceiptInterface, Invoice, Receipt};

class MyDriver extends Driver
{
    protected $invoice; // invoice

    protected $settings; // driver settings

    public function __construct(Invoice $invoice, $settings)
    {
        $this->invoice($invoice); // set the invoice
        $this->settings = (object) $settings; // set settings
    }

    // purchase the invoice, save its transactionId and finaly return it
    public function purchase() {
        // request for a payment transaction id
        ...
            
        $this->invoice->transactionId($transId);
        
        return $transId;
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
    public function verify() : ReceiptInterface {
        $verifyPayment = $this->settings->verifyApiUrl;
        
        $verifyUrl = $verifyPayment.$this->invoice->getTransactionId();
        
        ...
        
        /**
			then we send a request to $verifyUrl and if payment is not
			we throw an InvalidPaymentException with a suitable
        **/
        throw new InvalidPaymentException('a suitable message');
        
        /**
        	we create a receipt for this payment if everything goes normally.
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

you can listen for 2 events

- **InvoicePurchasedEvent** : occures when an invoice is purchased (after purchasing invoice is done successfully).
- **InvoiceVerifiedEvent** : occures when an invoice is verified successfully.

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

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
[link-packagist]: https://packagist.org/packages/shetabit/payment
[link-code-quality]: https://scrutinizer-ci.com/g/shetabit/payment
[link-author]: https://github.com/khanzadimahdi
[link-contributors]: ../../contributors
