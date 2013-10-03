<?php

namespace Prismic\Fragment;

class Date implements FragmentInterface
{
    private $value;

    function __construct($value)
    {
        $this->value = $value;
    }

    public function asHtml()
    {
        return '<time>' . $this->value . '</time>';
    }
}