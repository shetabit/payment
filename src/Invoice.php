<?php

namespace Shetabit\Payment;

use Ramsey\Uuid\Uuid;

class Invoice
{
    protected $uuid;

    /**
     * Amount
     *
     * @var int
     */
    protected $amount = 0;

    /**
     * invoice's transaction id
     *
     * @var string
     */
    protected $transactionId;

    /**
     * transactions ref id
     *
     * @var string
     */
    protected $refId;

    /**
     * Payment details
     *
     * @var string
     */
    protected $details = [];

    /**
     * @var string
     */
    protected $driver;

    /**
     * Invoice constructor.
     *
     * @throws \Exception
     */
    public function __construct()
    {
        $this->uuid();
    }

    /**
     * Set invoice uuid
     *
     * @param $uuid |null
     * @throws \Exception
     */
    public function uuid($uuid = null)
    {
        if (empty($uuid)) {
            $uuid = Uuid::uuid4()->toString();
        }

        $this->uuid = $uuid;
    }

    /**
     * Get invoice uuid
     *
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * Set a piece of data to the details.
     *
     * @param $key
     * @param $value |null
     * @return $this
     */
    public function detail($key, $value = null)
    {
        $key = is_array($key) ? $key : [$key => $value];

        foreach ($key as $k => $v) {
            $this->details[$k] = $v;
        }

        return $this;
    }

    /**
     * Get the value of details
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * Set the amount of invoice
     *
     * @param $amount
     * @return $this
     * @throws \Exception
     */
    public function amount($amount)
    {
        if (!is_int($amount)) {
            throw new \Exception('Amount value should be an integer.');
        }
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get the value of invoice
     *
     * @return int
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * set transaction id
     *
     * @param $id
     * @return $this
     */
    public function transactionId($id)
    {
        $this->transactionId = $id;

        return $this;
    }

    /**
     * Get the value of transaction's id
     *
     * @return string
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }

    /**
     * set ref id
     *
     * @param $id
     * @return $this
     */
    public function refId($id)
    {
        $this->refId = $id;

        return $this;
    }

    /**
     * Get the value of transaction's ref id
     *
     * @return string
     */
    public function getRefId()
    {
        return $this->refId;
    }

    /**
     * Set the value of driver
     *
     * @param $driver
     * @return $this
     */
    public function via($driver)
    {
        $this->driver = $driver;

        return $this;
    }

    /**
     * Get the value of driver
     *
     * @return string
     */
    public function getDriver()
    {
        return $this->driver;
    }
}
