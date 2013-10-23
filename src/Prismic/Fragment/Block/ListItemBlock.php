<?php

/*
 * This file is part of the Prismic PHP SDK
 *
 * Copyright 2013 Zengularity (http://www.zengularity.com).
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Prismic\Fragment\Block;

class ListItemBlock implements TextInterface
{

    private $text;
    private $spans;
    private $ordered;

    public function __construct($text, $spans, $ordered)
    {
        $this->text = $text;
        $this->spans = $spans;
        $this->ordered = $ordered;
    }

    public function getText()
    {
        return $this->text;
    }

    public function getSpans()
    {
        return $this->spans;
    }

    public function isOrdered()
    {
        return $this->ordered;
    }
}
