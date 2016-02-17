<?php

namespace TafDecoder\Entity;

class Temperature
{
    // annotation defining whether it's the minimum or maximum forecast temperature
    private $type;

    // temperature value
    private $temperature;

    // day of occurrence
    private $day;

    // hour of occurrence
    private $hour;


    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setTemperature($temperature)
    {
        $this->temperature = $temperature;

        return $this;
    }

    public function getTemperature()
    {
        return $this->temperature;
    }

    public function setDay($day)
    {
        $this->day = $day;

        return $this;
    }

    public function getDay()
    {
        return $this->day;
    }

    public function setHour($hour)
    {
        $this->hour = $hour;

        return $this;
    }

    public function getHour()
    {
        return $this->hour;
    }
}