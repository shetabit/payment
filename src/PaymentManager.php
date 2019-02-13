<?php

namespace Shetabit\Payment;

use Shetabit\Payment\Contracts\DriverInterface;
use Shetabit\Payment\Exceptions\DriverNotFoundException;
use Shetabit\Payment\Exceptions\InvoiceNotFoundException;

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
     * Payment Driver Instance.
     *
     * @var object
     */
    protected $driverInstance;

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
     * @param $amount
     * @return $this
     * @throws \Exception
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
     * Set transaction's id
     *
     * @param $id
     * @return $this
     */
    public function transactionId($id) {
        $this->invoice->transactionId($id);

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
        $this->driverInstance = $this->getFreshDriverInstance();
        if (!empty($initializeCallback)) {
            call_user_func($initializeCallback, $this->driverInstance);
        }

        //purchase the invoice
        $body = $this->driverInstance->purchase();
        if ($finalizeCallback) {
            call_user_func_array($finalizeCallback, [$this->driverInstance, $body]);
        }

        return $this;
    }

    /**
     * Pay the purchased invoice.
     *
     * @param null $initializeCallback
     * @return mixed
     * @throws \Exception
     */
    public function pay($initializeCallback = null)
    {
        $this->driverInstance = $this->getDriverInstance();
        if ($initializeCallback) {
            call_user_func($initializeCallback, $this->driverInstance);
        }
        $this->validateInvoice();

        return $this->driverInstance->pay();
    }

    /**
     * Verifies the payment
     *
     * @param $initializeCallback
     * @return mixed
     * @throws \Exception
     */
    public function verify($initializeCallback = null)
    {
        $this->driverInstance = $this->getDriverInstance();
        if (!empty($initializeCallback)) {
            call_user_func($initializeCallback, $this->driverInstance);
        }
        $this->validateInvoice();

        return $this->driverInstance->verify();
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
     * Retrieve current driver instance or generate new one.
     *
     * @return mixed
     * @throws \Exception
     */
    protected function getDriverInstance()
    {
        if (!empty($this->driverInstance)) {
            return $this->driverInstance;
        }

        return $this->getFreshDriverInstance();
    }

    /**
     * Get new driver instance
     *
     * @return mixed
     * @throws \Exception
     */
    protected function getFreshDriverInstance() {
        $this->validateDriver();
        $class = $this->config['map'][$this->driver];

        return new $class($this->invoice, $this->settings);
    }

    /**
     * Validate Invoice.
     *
     * @throws InvoiceNotFoundException
     */
    protected function validateInvoice() {
        if (empty($this->invoice)) {
            throw new InvoiceNotFoundException('Invoice not selected or does not exist.');
        }
    }

    /**
     * Validate driver.
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

        if (!class_exists($this->config['map'][$this->driver])) {
            throw new DriverNotFoundException('Driver source not found. Please update the package.');
        }

        $reflect = new \ReflectionClass($this->config['map'][$this->driver]);

        if (!$reflect->implementsInterface(Contracts\DriverInterface::class)) {
            throw new \Exception("Driver must be an instance of Contracts\DriverInterface.");
        }
    }
}
