<?php

namespace Prismic\Fragment;

class ParsedText
{
    private $text;
    private $spans;

    function __construct($text, $spans)
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