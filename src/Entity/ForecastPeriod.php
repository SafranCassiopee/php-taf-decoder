<?php

namespace TafDecoder\Entity;

class ForecastPeriod
{
    // starting day of forecast period
    private $from_day;

    // starting hour of forecast period
    private $from_hour;

    // ending day of forecast period
    private $to_day;

    // ending hour of forecast period
    private $to_hour;


    /**
     * Check if the forecast period is valid
     */
    public function isValid()
    {
        // check that attribute aren't null
        if (
            $this->getFromDay() == null || $this->getFromHour() === null ||
            $this->getToDay() == null || $this->getToHour() === null
        ) {
            return false;
        }

        // check ranges
        if ($this->getFromDay()  < 1 || $this->getFromDay() > 31) {
            return false;
        }
        if ($this->getToDay() < 1 || $this->getToDay() > 31) {
            return false;
        }
        if ($this->getFromHour() > 24 || $this->getToHour() > 24) {
            return false;
        }

        if ($this->getFromDay() == $this->getToDay() && $this->getFromHour() >= $this->getToHour()) {
            return false;
        }

        return true;
    }


    public function setFromDay($from_day)
    {
        $this->from_day = $from_day;

        return $this;
    }

    public function getFromDay()
    {
        return $this->from_day;
    }

    public function setFromHour($from_hour)
    {
        $this->from_hour = $from_hour;

        return $this;
    }

    public function getFromHour()
    {
        return $this->from_hour;
    }

    public function setToDay($to_day)
    {
        $this->to_day = $to_day;

        return $this;
    }

    public function getToDay()
    {
        return $this->to_day;
    }

    public function setToHour($to_hour)
    {
        $this->to_hour = $to_hour;

        return $this;
    }

    public function getToHour()
    {
        return $this->to_hour;
    }

}
