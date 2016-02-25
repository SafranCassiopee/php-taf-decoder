<?php

namespace TafDecoder\Entity;

class Evolution
{
    // annotation corresponding to the type of evolution (FM, BECMG or TEMPO)
    private $type;

    // day when the evolution occurs (FM) or starts (BECMG/TEMPO)
    private $from_day;

    // hour and minute UTC (as string) when the evolution occurs (FM)
    // or hour UTC (as string) when the evolution starts (BECMG/TEMPO)
    private $from_time;

    // day when the evolution ends (BECMG/tEMPO)
    private $to_day;

    // hour UTC (as string) when the evolution ends (BECMG/TEMPO)
    private $to_time;

    // weather entity (i.e. SurfaceWind, Temperature, Visibility, etc.)
    private $entity;
    private $cavok;

    // optional annotation corresponding to the probability (PROBnn)
    private $probability;

    // an evolution can contain embedded evolutions with different probabilities
    private $evolutions;


    public function __construct()
    {
        $this->evolutions = array();
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

    public function setFromDay($from_day)
    {
        $this->from_day = $from_day;

        return $this;
    }

    public function getFromDay()
    {
        return $this->from_day;
    }

    public function setFromTime($from_time)
    {
        $this->from_time = $from_time;

        return $this;
    }

    public function getFromTime()
    {
        return $this->from_time;
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

    public function setToTime($to_time)
    {
        $this->to_time = $to_time;

        return $this;
    }

    public function getToTime()
    {
        return $this->to_time;
    }

    public function setEntity($entity)
    {
        $this->entity = $entity;

        return $this;
    }

    public function getEntity()
    {
        return $this->entity;
    }

    public function setCavok($cavok)
    {
        $this->cavok = $cavok;

        return $this;
    }

    public function getCavok()
    {
        return $this->cavok;
    }

    public function setProbability($probability)
    {
        $this->probability = $probability;

        return $this;
    }

    public function getProbability()
    {
        return $this->probability;
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