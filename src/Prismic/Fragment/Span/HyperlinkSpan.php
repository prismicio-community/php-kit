<?php

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

    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }
}