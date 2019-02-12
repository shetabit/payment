<?php

namespace Shetabit\Payment;

use Shetabit\Payment\Exceptions\DriverNotFoundException;

class PaymentManager
{
    /**
     * Payment Configuration.
     *
     * @var array
     */
    protected $config;

    /**
     * Payment Driver Settings.
     *
     * @var array
     */
    protected $settings;

    /**
     * Payment Driver Name.
     *
     * @var string
     */
    protected $driver;

    /**
     * @var InvoiceBuilder
     */
    protected $invoice;

    /**
     * PaymentManager constructor.
     *
     * @param $config
     * @throws \Exception
     */
    public function __construct($config)
    {
        $this->config = $config;
        $this->setInvoice(new InvoiceBuilder());
        $this->via($this->config['default']);
    }

    /**
     * Set payment amount.
     *
     * @param integer $amount
     * @return self
     */
    public function amount($amount)
    {
        $this->invoice->amount($amount);

        return $this;
    }

    /**
     * Set a piece of data to the details.
     *
     * @param $key
     * @param null $value
     * @return $this
     */
    public function detail($key, $value = null)
    {
        $this->invoice->detail($key, $value);

        return $this;
    }

    /**
     * Change the driver on the fly.
     *
     * @param $driver
     * @return $this
     * @throws \Exception
     */
    public function via($driver)
    {
        $this->driver = $driver;
        $this->validateDriver();
        $this->invoice->via($driver);
        $this->settings = $this->config['drivers'][$driver];

        return $this;
    }

    /**
     * Purchase the invoice
     *
     * @param InvoiceBuilder $invoice
     * @param null $initializeCallback
     * @param null $finalizeCallback
     * @return $this
     * @throws \Exception
     */
    public function purchase(InvoiceBuilder $invoice, $initializeCallback = null, $finalizeCallback = null)
    {
        $this->setInvoice($invoice);

        $driver = $this->getDriverInstance();

        call_user_func($initializeCallback, $driver);

        //purchase the invoice
        $driver->purchase();

        call_user_func($finalizeCallback, $driver);

        return $this;
    }

    /**
     * Pay the purchased invoice.
     *
     * @param null $initializeCallback
     * @param null $finalizeCallback
     * @return $this
     */
    public function pay($initializeCallback = null)
    {
        if($initializeCallback)
            call_user_func($initializeCallback, $this->driver);

        return $this->driver->pay();
    }

    /**
     * Verifies the payment
     *
     * @return mixed
     */
    public function verify()
    {
        return $this->verify();
    }

    /**
     * @param InvoiceBuilder $invoice
     * @return self
     */
    protected function setInvoice(InvoiceBuilder $invoice)
    {
        $this->invoice = $invoice;

        return $this;
    }

    /**
     * Generate a new driver instance.
     *
     * @return mixed
     * @throws \Exception
     */
    protected function getDriverInstance()
    {
        $this->validateDriver();
        $class = $this->config['map'][$this->driver];

        return new $class($this->invoice,$this->settings);
    }

    /**
     * Validate Parameters before sending.
     *
     * @throws \Exception
     */
    protected function validateDriver()
    {
        if (empty($this->driver)) {
            throw new DriverNotFoundException('Driver not selected or default driver does not exist.');
        }

        if (empty($this->config['drivers'][$this->driver]) || empty($this->config['map'][$this->driver])) {
            throw new DriverNotFoundException('Driver not found in config file. Try updating the package.');
        }

        if (! class_exists($this->config['map'][$this->driver])) {
            throw new DriverNotFoundException('Driver source not found. Please update the package.');
        }

        $reflect = new \ReflectionClass($this->config['map'][$this->driver]);

        if (! $reflect->implementsInterface(Contracts\DriverInterface::class)) {
            throw new \Exception("Driver must be an instance of Contracts\DriverInterface.");
        }
    }
}
