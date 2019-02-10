<?php

namespace Shetabit\Payment\Drivers;

use GuzzleHttp\Client;
use Shetabit\Payment\Abstracts\Driver;

class Irankish extends Driver
{
    /**
     * Irankish Settings.
     *
     * @var object
     */
    protected $settings;

    /**
     * Irankish Client.
     *
     * @var object
     */
    protected $client;

    /**
     * Construct the class with the relevant settings.
     *
     * SendSmsInterface constructor.
     * @param $settings object
     */
    public function __construct($settings)
    {
        $this->settings = (object) $settings;
        $this->client = new Client();
    }

    public function purchase()
    {

    }

    public function pay()
    {

    }

    public function verify()
    {

    }
}
