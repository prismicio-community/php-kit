<?php

namespace Prismic\Fragment;

class Color implements FragmentInterface
{
    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function asHtml()
    {
        return '<span class="color">' . $this->data . '</span>';
    }
}