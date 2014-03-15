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

class Text implements FragmentInterface
{
    private $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function asHtml($linkResolver = null)
    {
        return '<span class="text">' . nl2br(htmlentities($this->value)) . '</span>';
    }

    public function asText()
    {
        return $this->getValue();
    }

    public function getValue()
    {
        return $this->value;
    }
}
