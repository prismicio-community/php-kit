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
 * This class embodies a span with a specific label.
 * By default, Prismic\Fragment\StructuredText::insertSpans will render these
 * as a span element with the label name as the class attribute.
 */
class LabelSpan implements SpanInterface
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
     * @var string the label
     */
    private $label;

    /**
     * Constructs a label span
     *
     * @param integer $start the start of the span
     * @param integer $end the end of the span
     * @param string  $label The value of the label in the writing room
     */
    public function __construct($start, $end, $label)
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
