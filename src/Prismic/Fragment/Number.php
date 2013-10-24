<?php

/*
 * This file is part of the Prismic PHP SDK
 *
 * Copyright 2013 Zengularity (http://www.zengularity.com).
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Prismic\Fragment;

class Number implements FragmentInterface
{
    private $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function asText($pattern)
    {
        return sprintf($pattern, $this->value);
    }

    public function asHtml()
    {
        return '<span class="number">' . $this->value . '</span>';
    }

    public function getValue()
    {
        return $this->value;
    }

    public function __toString()
    {
        return (string)$this->getValue();
    }
}
