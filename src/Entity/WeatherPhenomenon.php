<?php

namespace TafDecoder\Entity;

class WeatherPhenomenon
{
    // intensity/proximity of the phenomenon + / - / VC (heavy, light, vicinity)
    private $intensity_proximity;

    // characteristics of the phenomenon
    private $descriptor;

    // types of phenomenon
    private $phenomenons;


    public function __construct()
    {
        $this->phenomenons = array();
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
        $this->phenomenons[] = $phenomenon;

        return $this;
    }

    public function getPhenomenons()
    {
        return $this->phenomenons;
    }
}
