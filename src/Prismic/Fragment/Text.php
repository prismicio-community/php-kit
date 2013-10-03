<?php

namespace Prismic\Fragment;

class Text implements FragmentInterface
{
    private $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function asHtml()
    {
        return '<span class="text">' . $this->value . '</span>';
    }
}