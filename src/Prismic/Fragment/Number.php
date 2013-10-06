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