<?php

namespace Prismic\Fragment;

class ImageBlock implements BlockInterface
{

    private $view;

    function __construct($view)
    {
        $this->view = $view;
    }

    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }
}