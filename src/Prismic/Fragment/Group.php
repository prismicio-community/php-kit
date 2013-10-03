<?php

namespace Prismic\Fragment;

class Group
{
    private $maybeTag;
    private $blocks;

    public function __construct($maybeTag, $blocks)
    {
        $this->maybeTag = $maybeTag;
        $this->blocks = $blocks;
    }

    public function addBlock($block)
    {
        array_push($this->blocks, $block);
    }

    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }
}