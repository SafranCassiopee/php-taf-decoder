<?php

namespace TafDecoder\Entity;

class DecodedTaf
{
    // raw TAF
    private $raw_taf;

    // decoding exceptions, if any
    private $decoding_exceptions = array();

    // report type (TAF or TAF AMD)
    private $type;

    // ICAO code of the airport where the forecast has been made
    private $icao;

    // day of origin
    private $day;

    // time of origin, as string
    private $time;

    // forecast period
    private $forecast_period;

    // surface wind
    private $surface_wind;

    // visibility information
    private $visibility;
    private $cavok;


    public function __construct($raw_taf)
    {
        $this->raw_taf = $raw_taf;

        $this->cavok = false;

        $this->decoding_exceptions = array();
    }

    /**
     * Check if the decoded taf is valid, i.e. if there was no error during decoding
     */
    public function isValid()
    {
        return (count($this->decoding_exceptions) == 0);
    }

    /**
     * Add an exception that occurred during taf decoding
     */
    public function addDecodingException($exception)
    {
        $this->decoding_exceptions[] = $exception;

        return $this;
    }

    /**
     * If the decoded taf is invalid, get all the exceptions that occurred during decoding
     * Note that in strict mode, only the first encountered exception will be reported as parsing stops on error
     * Else return null;
     */
    public function getDecodingExceptions()
    {
        return $this->decoding_exceptions;
    }

    public function resetDecodingExceptions()
    {
        $this->decoding_exceptions = array();
    }

    public function getRawTaf()
    {
        return trim($this->raw_taf);
    }

    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setIcao($icao)
    {
        $this->icao = $icao;

        return $this;
    }

    public function getIcao()
    {
        return $this->icao;
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

    public function setTime($time)
    {
        $this->time = $time;

        return $this;
    }

    public function getTime()
    {
        return $this->time;
    }

    public function setForecastPeriod($forecast_period)
    {
        $this->forecast_period = $forecast_period;

        return $this;
    }

    public function getForecastPeriod()
    {
        return $this->forecast_period;
    }

    public function setSurfaceWind($surface_wind)
    {
        $this->surface_wind = $surface_wind;

        return $this;
    }

    public function getSurfaceWind()
    {
        return $this->surface_wind;
    }

    public function setVisibility(Visibility $visibility)
    {
        $this->visibility = $visibility;

        return $this;
    }

    public function getVisibility()
    {
        return $this->visibility;
    }

    public function setCavok($cavok)
    {
        $this->cavok = $cavok;

        return;
    }

    public function getCavok()
    {
        return $this->cavok;
    }

}
