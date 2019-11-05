<?php

namespace Shetabit\Payment\Traits;

trait HasDetail
{
    /**
     * details
     *
     * @var array
     */
    protected $details = [];

    /**
     * Set a piece of data to the details.
     *
     * @param $key
     * @param $value |null
     *
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
}
