<?php

namespace Prismic\Fragment\Block;

class EmbedBlock implements BlockInterface
{
    private $obj;

    public function __construct($obj)
    {
        $this->obj = $obj;
    }

    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }
}