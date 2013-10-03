<?php

namespace Prismic\Fragment;

class Number implements FragmentInterface
{
    private $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function asText()
    {
        return $this->data;
    }

    public function asHtml()
    {
        return '<span class="number">' . $this->data . '</span>';
    }
}