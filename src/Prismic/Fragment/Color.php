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

class Color implements FragmentInterface
{
    private $hex;

    public function __construct($hex)
    {
        $this->hex = $hex;
    }

    public function asHtml($linkResolver = null)
    {
        return '<span class="color">' . htmlentities($this->hex) . '</span>';
    }

    public function asText()
    {
        return $this->getHexValue();
    }

    public function getHexValue()
    {
        return $this->hex;
    }
}
