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

class Date implements FragmentInterface
{
    private $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function asHtml($linkResolver = null)
    {
        return '<time>' . htmlentities($this->value) . '</time>';
    }

    public function asText()
    {
        return $this->value;
    }

    public function formatted($pattern)
    {
        return date($pattern, $this->asEpoch());
    }

    public function asEpoch()
    {
        return strtotime($this->value);
    }

    public function getValue()
    {
        return $this->value;
    }
}
