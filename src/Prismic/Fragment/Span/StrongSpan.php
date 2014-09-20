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

/**
 * This class embodies a strong span (typically rendered as bold).
 * A span comes in a array of spans, which is served with a raw text. If the raw text is
 * "Hello world!", and the StrongSpan's start is 6 and its end is 11, then the piece that
 * is meant to be bold is "world".
 */
class StrongSpan implements SpanInterface
{

    /**
     * @var integer the start of the span
     */
    private $start;
    /**
     * @var integer the end of the span
     */
    private $end;
    /**
     * @var string the label (optional, may be null)
     */
    private $label;

    /**
     * Constructs an strong span
     *
     * @param integer $start the start of the span
     * @param integer $end the end of the span
     * @param $label
     */
    public function __construct($start, $end, $label = NULL)
    {
        $this->start = $start;
        $this->end = $end;
        $this->label = $label;
    }

    /**
     * Returns the start of the span
     *
     * @api
     *
     * @return integer the start of the span
     */
    public function getStart()
    {
        return $this->start;
    }

    /**
     * Returns the end of the span
     *
     * @api
     *
     * @return integer the end of the span
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * Returns the label
     *
     * @api
     *
     * @return string the label
     */
    public function getLabel()
    {
        return $this->label;
    }
}
