<p align="center"><img src="resources/images/payment.png?raw=true"></p>



# Laravel 支付网关



[![Software License][ico-license]](LICENSE.md)
[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads on Packagist][ico-download]][link-packagist]
[![StyleCI](https://github.styleci.io/repos/169948762/shield?branch=master)](https://github.styleci.io/repos/169948762)
[![Maintainability](https://api.codeclimate.com/v1/badges/e6a80b17298cb4fcb56d/maintainability)](https://codeclimate.com/github/shetabit/payment/maintainability)
[![Quality Score][ico-code-quality]][link-code-quality]

这是一个用于整合支付网关的Laravel包。这个包依赖 `Laravel 5.8+`.

[捐赠我](https://yekpay.me/mahdikhanzadi) 如果你喜欢这个包:sunglasses: :bowtie:

For PHP integration you can use [shetabit/multipay](https://github.com/shetabit/multipay) package.

> 此软件包可用于多个驱动程序，如果在[当前驱动程序列表](#list-of-available-drivers)中找不到驱动程序，则可以创建它们

- [داکیومنت فارسی][link-fa]
- [English documents][link-en]
- [中文文档][link-zh]

# 目录

- [Laravel 支付网关](#laravel-%e6%94%af%e4%bb%98%e7%bd%91%e5%85%b3)
- [目录](#%e7%9b%ae%e5%bd%95)
- [可用驱动列表](#%e5%8f%af%e7%94%a8%e9%a9%b1%e5%8a%a8%e5%88%97%e8%a1%a8)
  - [安装](#%e5%ae%89%e8%a3%85)
  - [配置](#%e9%85%8d%e7%bd%ae)
  - [如何使用](#%e5%a6%82%e4%bd%95%e4%bd%bf%e7%94%a8)
      - [使用费用清单进行工作](#%e4%bd%bf%e7%94%a8%e8%b4%b9%e7%94%a8%e6%b8%85%e5%8d%95%e8%bf%9b%e8%a1%8c%e5%b7%a5%e4%bd%9c)
      - [获取支付清单](#%e8%8e%b7%e5%8f%96%e6%94%af%e4%bb%98%e6%b8%85%e5%8d%95)
      - [支付](#%e6%94%af%e4%bb%98)
      - [验证付款](#%e9%aa%8c%e8%af%81%e4%bb%98%e6%ac%be)
      - [有用的方法](#%e6%9c%89%e7%94%a8%e7%9a%84%e6%96%b9%e6%b3%95)
      - [创建自定义驱动:](#%e5%88%9b%e5%bb%ba%e8%87%aa%e5%ae%9a%e4%b9%89%e9%a9%b1%e5%8a%a8)
      - [事件](#%e4%ba%8b%e4%bb%b6)
  - [Change log](#change-log)
  - [贡献](#%e8%b4%a1%e7%8c%ae)
  - [安全](#%e5%ae%89%e5%85%a8)
  - [信誉](#%e4%bf%a1%e8%aa%89)
  - [License](#license)

# 可用驱动列表

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
- [paypal](http://www.paypal.com/) (在下一个版本中很快就支持了)
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
- 其他正在进行中

**您可以通过`pull requests` 帮助我创建更多的网关**

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

> 如果找不到你需要的，您可以创建你自己的驱动，阅读`创建自定义驱动`部分，可以了解更多

## 安装

通过 Composer

``` bash
$ composer require shetabit/payment
```

## 配置

如果你使用`Laravel 5.5`或更高版本，你不需要手动设置 `provider` 和 `alias`,可以直接看b步骤

a. 在你的 `config/app.php` 文件中，添加如下两行

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

b. 然后运行 `php artisan vendor:publish` 来发布 `config/payment.php` 文件到你的项目中

在配置文件中，您可以将 `default`设置项设置为你希望的付款方式。但也可以在运行时更改驱动。

选择要在应用程序中使用的网关。然后将其设为默认驱动程序，这样就不必在任何地方都指定它。但是，您也可以在一个项目中使用多个网关。


```php
// Eg. if you want to use zarinpal.
'default' => 'zarinpal',
```

然后在驱动数组中填充该网关的凭据。

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

## 如何使用

您的 `Invoice` 包含您的付款详细信息，因此我们首先将讨论 `Invoice` 类。


#### 使用费用清单进行工作

在做任何事情之前，您需要使用 `Invoice` 类来创建费用清单。

像下面这样，在你的代码中使用费用清单:

```php
// At the top of the file.
use Shetabit\Multipay\Invoice;
...

// Create new invoice.
$invoice = new Invoice;

// 设置清单金额.
$invoice->amount(1000);

// 给清单添加详情: 这里展示了四种语法.
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
可用方法:

- `uuid`: 设置一个清单的唯一id
- `getUuid`: 获取清单的当前唯一id
- `detail`: 给清单添加自定义信息
- `getDetails`: 获取所有的详细信息
- `amount`: 设置一个清单金额
- `getAmount`: 获取清单金额
- `transactionId`: 设置支付交易单号
- `getTransactionId`: 获取支付交易单号
- `via`: 设置我们用来支付清单的驱动
- `getDriver`: 获取驱动

#### 获取支付清单

为了支付清单，我们需要付款交易ID:

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

#### 支付

在获取支付单后，我们可以跳转到第三方支付机构的页面

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
        // 把交易ID保存到数据库.
        // 在接下来的付款中，我们需要验证交易ID
	}
)->pay()->render();

// Retrieve json format of Redirection (in this case you can handle redirection to bank gateway)
return Payment::purchase(
    (new Invoice)->amount(1000), 
    function($driver, $transactionId) {
        // 把交易ID保存到数据库.
        // 在接下来的付款中，我们需要验证交易ID
	}
)->pay()->toJson();
```

#### 验证付款

当用户完成付款后，支付机构会将其重定向到您的网站，然后您需要**验证您的付款**，以确保**清单**已**支付**。

```php
// At the top of the file.
use Shetabit\Payment\Facade\Payment;
use Shetabit\Multipay\Exceptions\InvalidPaymentException;
...

// 您需要验证支付机构的回传数据，以确保付款成功
// 我们需要使用交易ID来验证
// 使用交易金额来验证，也是一个很好的方法
try {
	$receipt = Payment::amount(1000)->transactionId($transaction_id)->verify();

    // You can show payment referenceId to the user.
    echo $receipt->getReferenceId();

    ...
} catch (InvalidPaymentException $exception) {
    /**
    	如果未验证付款，则会引发异常。

我们可以抓住异常处理无效付款。

getMessage方法，返回可在用户界面中使用的适当消息。
    **/
    echo $exception->getMessage();
}
```

#### 有用的方法

- ###### `callbackUrl`: 使用它可以在运行时改变回调地址.

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

- ###### `amount`: 你可以设置一个清单的金额

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

- ###### `via`: 在运行中更改支付方式

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
  
- ###### `config`: 在运行中更改驱动的配置信息

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

#### 创建自定义驱动:

首先必须在`drivers`数组中添加驱动程序的名称，还可以指定所需的任何配置参数。

```php
'drivers' => [
    'zarinpal' => [...],
    'my_driver' => [
        ... // Your Config Params here.
    ]
]
```

现在您必须创建一个将用于支付清单的驱动程序映射类。
在你的驱动中，你必须继承 `Shetabit\Payment\Abstracts\Driver`.这个类

例如，你创建了这样一个类: `App\Packages\PaymentDriver\MyDriver`。

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
创建该类后，必须在 `payment.php` 配置文件的 `map` 部分中指定它。

```php
'map' => [
    ...
    'my_driver' => App\Packages\PaymentDriver\MyDriver::class,
]
```

**Note:-** 必须确保 `map` 数组的键与 `drivers` 数组的键相同。

#### 事件

你可以监听两个事件

- **InvoicePurchasedEvent**: 在获取清单后执行
- **InvoiceVerifiedEvent**: 在验证交易成功后执行

## Change log

请查看 [CHANGELOG](CHANGELOG.md) 来获取更多关于版本更新的信息

## 贡献

请查看 [CONTRIBUTING](CONTRIBUTING.md) 和 [CONDUCT](CONDUCT.md) 获取更多详细信息

## 安全

如果您发现任何与安全相关的问题，请发送电子邮件至`khanzadimahdi@gmail.com`，而不要使用issue。

## 信誉

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
