<?php

/*
 * This file is part of the Prismic PHP SDK
 *
 * Copyright 2013 Zengularity (http://www.zengularity.com).
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Prismic\Fragment\Span;

class HyperlinkSpan implements SpanInterface
{

    private $start;
    private $end;
    private $link;

    public function __construct($start, $end, $link)
    {
        $this->start = $start;
        $this->end = $end;
        $this->link = $link;
    }

    public function getStart()
    {
        return $this->start;
    }

    public function getEnd()
    {
        return $this->end;
    }

    public function getLink()
    {
        return $this->link;
    }
}
