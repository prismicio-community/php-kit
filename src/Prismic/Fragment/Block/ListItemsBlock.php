<?php

namespace Prismic\Fragment\Block;

class ListItemBlock implements BlockInterface
{

    private $text;
    private $spans;
    private $ordered;

    public function __construct($text, $spans, $ordered)
    {
        $this->text = $text;
        $this->spans = $spans;
        $this->ordered = $ordered;
    }

    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }
}