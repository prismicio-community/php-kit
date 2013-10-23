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

class HeadingBlock implements TextInterface
{
    private $text;
    private $spans;
    private $level;

    public function __construct($text, $spans, $level)
    {
        $this->text = $text;
        $this->spans = $spans;
        $this->level = $level;
    }

    public function getText()
    {
        return $this->text;
    }

    public function getSpans()
    {
        return $this->spans;
    }

    public function getLevel()
    {
        return $this->level;
    }
}
