<?php
/**
 * This file is part of the Prismic PHP SDK
 *
 * Copyright 2013 Zengularity (http://www.zengularity.com).
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Prismic\Fragment\Span;

use Prismic\Fragment\Link\LinkInterface;

/**
 * This class embodies a link span.
 * A span comes in a array of spans, which is served with a raw text. If the raw text is
 * "Hello world!", and the HyperlinkSpan's start is 6 and its end is 11, then the piece that
 * is meant to be the hypertext is "world".
 */
class HyperlinkSpan implements SpanInterface
{

    /**
     * @var int the start of the span
     */
    private $start;
    /**
     * @var int the end of the span
     */
    private $end;
    /**
     * @var LinkInterface the link to point to
     */
    private $link;
    /**
     * @var string the label (optional, may be null)
     */
    private $label;
    /**
     * @var string the target (optional, may be null)
     */
    private $target;

    /**
     * Constructs a link span
     *
     * @param int           $start the start of the span
     * @param int           $end   the end of the span
     * @param LinkInterface $link  the link to point to
     * @param string        $label can be null
     * @param string        $target can be null
     */
    public function __construct($start, $end, $link, $label = NULL, $target = NULL)
    {
        $this->start = $start;
        $this->end = $end;
        $this->link = $link;
        $this->label = $label;
        $this->target = $target;
    }

    /**
     * Returns the start of the span
     *
     * 
     *
     * @return int the start of the span
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * Returns the end of the span
     *
     * 
     *
     * @return int the end of the span
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * Returns the link to point to
     *
     * 
     *
     * @return LinkInterface the link to point to
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * Returns the label
     *
     * 
     *
     * @return string the label
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Returns the target
     *
     * 
     *
     * @return string the target
     */
    public function getTarget()
    {
        return $this->target;
    }
}
