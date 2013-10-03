<?php

namespace Prismic\Fragment\Block;

class HeadingBlock implements BlockInterface
{
    private $text;
    private $spans;
    private $level;

    public function __construct($text, $spans, $level)
    {
        $this->text = $text;
        $this->spans = $spans;
        $this->level = $level;
    }

    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }
}