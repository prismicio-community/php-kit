<?php

namespace Prismic\Fragment\Block;

class ParagraphBlock implements BlockInterface
{
    private $text;
    private $spans;

    public function __construct($text, $spans)
    {
        $this->text = $text;
        $this->spans = $spans;
    }

    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }
}