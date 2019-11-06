<?php
return [
    /*
    |--------------------------------------------------------------------------
    | Default Driver
    |--------------------------------------------------------------------------
    |
    | This value determines which of the following gateway to use.
    | You can switch to a different driver at runtime.
    |
    */
    'default' => 'zarinpal',

    /*
    |--------------------------------------------------------------------------
    | List of Drivers
    |--------------------------------------------------------------------------
    |
    | These are the list of drivers to use for this package.
    | You can change the name. Then you'll have to change
    | it in the map array too.
    |
    */
    'drivers' => [
        'asanpardakht' => [
            'apiPurchaseUrl' => 'https://services.asanpardakht.net/paygate/merchantservices.asmx?wsdl',
            'apiPaymentUrl' => 'https://asan.shaparak.ir',
            'apiVerificationUrl' => 'https://services.asanpardakht.net/paygate/merchantservices.asmx?wsdl',
            'merchantId' => '',
            'callbackUrl' => 'http://yoursite.com/path/to',
            'description' => 'payment in '.config('app.name'),
        ],
        'behpardakht' => [
            'apiPurchaseUrl' => 'https://bpm.shaparak.ir/pgwchannel/services/pgw?wsdl',
            'apiPaymentUrl' => 'https://bpm.shaparak.ir/pgwchannel/startpay.mellat',
            'apiVerificationUrl' => 'https://api.nextpay.org/gateway/verify.http',
            'apiNamespaceUrl' => 'http://interfaces.core.sw.bps.com/',
            'terminalId' => '',
            'username' => '',
            'password' => '',
            'callbackUrl' => 'http://yoursite.com/path/to',
            'description' => 'payment in '.config('app.name'),
        ],
        'idpay' => [
            'apiPurchaseUrl' => 'https://api.idpay.ir/v1.1/payment',
            'apiPaymentUrl' => 'https://idpay.ir/p/ws/',
            'apiSandboxPaymentUrl' => 'https://idpay.ir/p/ws-sandbox/',
            'apiVerificationUrl' => 'https://api.idpay.ir/v1.1/payment/verify',
            'merchantId' => '',
            'callbackUrl' => 'http://yoursite.com/path/to',
            'description' => 'payment in '.config('app.name'),
            'sandbox' => false, // set it to true for test environments
        ],
        'irankish' => [
            'apiPurchaseUrl' => 'https://ikc.shaparak.ir/XToken/Tokens.xml',
            'apiPaymentUrl' => 'https://ikc.shaparak.ir/TPayment/Payment/index/',
            'apiVerificationUrl' => 'https://ikc.shaparak.ir/XVerify/Verify.xml',
            'merchantId' => '',
            'sha1Key' => '',
            'callbackUrl' => 'http://yoursite.com/path/to',
            'description' => 'payment in '.config('app.name'),
        ],
        'melli' => [
            'apiPurchaseUrl' => 'https://sadad.shaparak.ir/vpg/api/v0/Request/PaymentRequest',
            'apiPaymentUrl' => 'https://sadad.shaparak.ir/VPG/Purchase',
            'apiVerificationUrl' => 'https://sadad.shaparak.ir/VPG/api/v0/Advice/Verify',
            'merchantId' => '',
            'callbackUrl' => 'http://yoursite.com/path/to',
            'description' => 'payment in '.config('app.name'),
        ],
        'nextpay' => [
            'apiPurchaseUrl' => 'https://api.nextpay.org/gateway/token.http',
            'apiPaymentUrl' => 'https://api.nextpay.org/gateway/payment/',
            'apiVerificationUrl' => 'https://api.nextpay.org/gateway/verify.http',
            'merchantId' => '',
            'callbackUrl' => 'http://yoursite.com/path/to',
            'description' => 'payment in '.config('app.name'),
        ],
        'parsian' => [
            'apiPurchaseUrl' => 'https://pec.shaparak.ir/NewIPGServices/Sale/SaleService.asmx?wsdl',
            'apiPaymentUrl' => 'https://pec.shaparak.ir/NewIPG/',
            'apiVerificationUrl' => 'https://pec.shaparak.ir/NewIPGServices/Confirm/ConfirmService.asmx?wsdl',
            'loginAccount' => '',
            'orderId' => '',
            'callbackUrl' => 'http://yoursite.com/path/to',
            'description' => 'payment in '.config('app.name'),
        ],
        'pasargad' => [
            'apiPurchaseUrl' => 'https://pep.shaparak.ir/Api/v1/Payment/GetToken',
            'apiPaymentUrl' => 'https://pep.shaparak.ir/payment.aspx',
            'apiVerificationUrl' => 'https://pep.shaparak.ir/Api/v1/Payment/CheckTransactionResult',
            'merchantId' => '',
            'callbackUrl' => 'http://yoursite.com/path/to',
            'description' => 'payment in '.config('app.name'),
        ],
        'payir' => [
            'apiPurchaseUrl' => 'https://pay.ir/pg/send/',
            'apiPaymentUrl' => 'https://pay.ir/pg/',
            'apiVerificationUrl' => 'https://pay.ir/pg/verify/',
            'merchantId' => '', // set it to `test` for test environments
            'callbackUrl' => 'http://yoursite.com/path/to',
            'description' => 'payment in '.config('app.name'),
        ],
        'payping' => [
            'apiPurchaseUrl' => 'https://api.payping.ir/v1/pay/',
            'apiPaymentUrl' => 'https://api.payping.ir/v1/pay/gotoipg/',
            'apiVerificationUrl' => 'https://api.payping.ir/v1/pay/verify/',
            'merchantId' => '',
            'callbackUrl' => 'http://yoursite.com/path/to',
            'description' => 'payment in '.config('app.name'),
        ],
        'paystar' => [
            'apiPurchaseUrl' => 'https://paystar.ir/api/create/',
            'apiPaymentUrl' => 'https://paystar.ir/paying/',
            'apiVerificationUrl' => 'https://paystar.ir/api/verify/',
            'merchantId' => '',
            'callbackUrl' => 'http://yoursite.com/path/to',
            'description' => 'payment in '.config('app.name'),
        ],
        'poolam' => [
            'apiPurchaseUrl' => 'https://poolam.ir/invoice/request/',
            'apiPaymentUrl' => 'https://poolam.ir/invoice/pay/',
            'apiVerificationUrl' => 'https://poolam.ir/invoice/check/',
            'merchantId' => '',
            'callbackUrl' => 'http://yoursite.com/path/to',
            'description' => 'payment in '.config('app.name'),
        ],
        'saderat' => [
            'apiPurchaseUrl' => 'https://mabna.shaparak.ir/TokenService?wsdl',
            'apiPaymentUrl' => 'https://mabna.shaparak.ir/',
            'apiVerificationUrl' => 'https://mabna.shaparak.ir/TransactionReference/TransactionReference?wsdl',
            'merchantId' => '',
            'callbackUrl' => 'http://yoursite.com/path/to',
            'description' => 'payment in '.config('app.name'),
        ],
        'saman' => [
            'apiPurchaseUrl' => 'https://sep.shaparak.ir/Payments/InitPayment.asmx?WSDL',
            'apiPaymentUrl' => 'https://sep.shaparak.ir/payment.aspx',
            'apiVerificationUrl' => 'https://sep.shaparak.ir/payments/referencepayment.asmx?WSDL',
            'merchantId' => '',
            'callbackUrl' => '',
            'description' => 'payment in '.config('app.name'),
        ],
        'yekpay' => [
            'apiPurchaseUrl' => 'https://gate.yekpay.com/api/payment/server?wsdl',
            'apiPaymentUrl' => 'https://gate.yekpay.com/api/payment/start/',
            'apiVerificationUrl' => 'https://gate.yekpay.com/api/payment/server?wsdl',
            'merchantId' => '',
            'callbackUrl' => 'http://yoursite.com/path/to',
            'description' => 'payment in '.config('app.name'),
        ],
        'zarinpal' => [
            /* normal api */
            'apiPurchaseUrl' => 'https://ir.zarinpal.com/pg/services/WebGate/wsdl',
            'apiPaymentUrl' => 'https://www.zarinpal.com/pg/StartPay/',
            'apiVerificationUrl' => 'https://ir.zarinpal.com/pg/services/WebGate/wsdl',

            /* sandbox api */
            'sandboxApiPurchaseUrl' => 'https://sandbox.zarinpal.com/pg/rest/WebGate/PaymentRequest.json',
            'sandboxApiPaymentUrl' => 'https://sandbox.zarinpal.com/pg/StartPay/',
            'sandboxApiVerificationUrl' => 'https://sandbox.zarinpal.com/pg/services/WebGate/wsdl',

            /* zarinGate api */
            'zaringateApiPurchaseUrl' => 'https://ir.zarinpal.com/pg/services/WebGate/wsdl',
            'zaringateApiPaymentUrl' => 'https://www.zarinpal.com/pg/StartPay/:authority/ZarinGate',
            'zaringateApiVerificationUrl' => 'https://ir.zarinpal.com/pg/services/WebGate/wsdl',

            'mode' => 'normal', // can be normal, sandbox, zaringate
            'merchantId' => '',
            'callbackUrl' => 'http://yoursite.com/path/to',
            'description' => 'payment in '.config('app.name'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Class Maps
    |--------------------------------------------------------------------------
    |
    | This is the array of Classes that maps to Drivers above.
    | You can create your own driver if you like and add the
    | config in the drivers array and the class to use for
    | here with the same name. You will have to extend
    | Shetabit\Payment\Abstracts\Driver in your driver.
    |
    */
    'map' => [
        'asanpardakht' => \Shetabit\Payment\Drivers\Asanpardakht::class,
        'behpardakht' => \Shetabit\Payment\Drivers\Behpardakht::class,
        'idpay' => \Shetabit\Payment\Drivers\Idpay::class,
        'irankish' => \Shetabit\Payment\Drivers\Irankish::class,
        'melli' => \Shetabit\Payment\Drivers\Melli::class,
        'nextpay' => \Shetabit\Payment\Drivers\Nextpay::class,
        'parsian' => \Shetabit\Payment\Drivers\Parsian::class,
        'pasargad' => \Shetabit\Payment\Drivers\Pasargad::class,
        'payir' => \Shetabit\Payment\Drivers\Payir::class,
        'payping' => \Shetabit\Payment\Drivers\Payping::class,
        'paystar' => \Shetabit\Payment\Drivers\Paystar::class,
        'poolam' => \Shetabit\Payment\Drivers\Poolam::class,
        'saderat' => \Shetabit\Payment\Drivers\Saderat::class,
        'saman' => \Shetabit\Payment\Drivers\Saman::class,
        'yekpay' => \Shetabit\Payment\Drivers\Yekpay::class,
        'zarinpal' => \Shetabit\Payment\Drivers\Zarinpal::class,
    ]
];
