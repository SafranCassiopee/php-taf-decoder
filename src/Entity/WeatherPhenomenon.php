<?php

namespace TafDecoder\Entity;

class WeatherPhenomenon extends InformationBase
{
    // intensity/proximity of the phenomenon + / - / VC (heavy, light, vicinity)
    private $intensity_proximity;

    // characteristics of the phenomenon
    private $descriptor;

    // types of phenomena
    private $phenomena;

    // array of Evolution entities
    private $evolutions;


    public function __construct()
    {
        $this->phenomena = array();
        $this->evolutions = array();
    }


    public function setIntensityProximity($intensity_proximity)
    {
        $this->intensity_proximity = $intensity_proximity;

        return $this;
    }

    public function getIntensityProximity()
    {
        return $this->intensity_proximity;
    }

    public function setDescriptor($desc)
    {
        $this->descriptor = $desc;

        return $this;
    }

    public function getDescriptor()
    {
        return $this->descriptor;
    }

    public function addPhenomenon($phenomenon)
    {
        $this->phenomena[] = $phenomenon;

        return $this;
    }

    public function getPhenomena()
    {
        return $this->phenomena;
    }

    public function addEvolution($evolution)
    {
        $this->evolutions[] = $evolution;

        return $this;
    }

    public function getEvolutions()
    {
        return $this->evolutions;
    }
}
