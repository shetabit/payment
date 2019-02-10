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
     * @var PaymentBuilder
     */
    protected $builder;

    /**
     * PaymentManager constructor.
     *
     * @param $config
     */
    public function __construct($config)
    {
        $this->config = $config;
        $this->setBuilder(new PaymentBuilder());
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
        $this->builder->amount($amount);

        return $this;
    }

    /**
     * Set a piece of data to the details.
     *
     * @param $key
     * @param null $value
     * @return $this
     */
    public function with($key, $value = null)
    {
        $this->builder->with($key, $value);

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
        $this->builder->via($driver);
        $this->settings = $this->config['drivers'][$driver];

        return $this;
    }

    /**
     * Send message.
     *
     * @param $payment
     * @param $callback
     * @return mixed
     * @throws \Exception
     */
    public function send($payment, $callback = null)
    {
        if ($payment instanceof PaymentBuilder) {
            return $this->setBuilder($payment)->dispatch();
        }

        $this->builder->send($payment);
        if (! $callback) {
            return $this;
        }

        $driver = $this->getDriverInstance();
        $driver->amount($payment);
        call_user_func($callback, $driver);

        return $driver->send();
    }

    /**
     * @return mixed
     */
    public function dispatch()
    {
        $this->driver = $this->builder->getDriver() ?: $this->driver;
        if (empty($this->driver)) {
            $this->via($this->config['default']);
        }
        $driver = $this->getDriverInstance();
        $driver->with($this->builder->getDetails());
        $driver->amount($this->builder->getAmount());

        return $driver->send();
    }

    /**
     * @param PaymentBuilder $builder
     * @return self
     */
    protected function setBuilder(PaymentBuilder $builder)
    {
        $this->builder = $builder;

        return $this;
    }

    /**
     * Generate driver instance.
     *
     * @return mixed
     */
    protected function getDriverInstance()
    {
        $this->validateDriver();
        $class = $this->config['map'][$this->driver];

        return new $class($this->settings);
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
