<?php

namespace TafDecoder\Entity;

class Visibility
{
    // prevailing visibility
    private $visibility;

    // visibility is greater than the given value
    private $greater;


    public function __construct()
    {
        $this->greater = false;
    }


    public function setVisibility($visibility)
    {
        $this->visibility = $visibility;

        return $this;
    }

    public function getVisibility()
    {
        return $this->visibility;
    }

    public function setGreater($greater)
    {
        $this->greater = $greater;

        return $this;
    }

    public function getGreater()
    {
        return $this->greater;
    }
}
