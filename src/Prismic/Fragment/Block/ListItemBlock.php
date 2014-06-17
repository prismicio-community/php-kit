<?php
/**
 * This file is part of the Prismic PHP SDK
 *
 * Copyright 2013 Zengularity (http://www.zengularity.com).
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Prismic\Fragment\Block;

/**
 * This class embodies a list item block inside a StructuredText fragment.
 */
class ListItemBlock implements TextInterface
{

    /**
     * @var string the unformatted text of the list item
     */
    private $text;
    /**
     * @var array an array of \Prismic\Fragment\Span\SpanInterface objects that contain the formatting (em, strong, links, ...)
     */
    private $spans;
    /**
     * @var boolean true if part of an ordered list, false if unordered
     */
    private $ordered;

    /**
     * Constructs a list item block.
     *
     * @param string  $text    the unformatted text
     * @param array   $spans   an array of \Prismic\Fragment\Span\SpanInterface objects that contain the formatting (em, strong, links, ...)
     * @param boolean $ordered true if part of an ordered list, false if unordered
     */
    public function __construct($text, $spans, $ordered)
    {
        $this->text = $text;
        $this->spans = $spans;
        $this->ordered = $ordered;
    }

    /**
     * Returns the unformatted text.
     *
     * @api
     *
     * @return string the unformatted text.
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Returns the formatting (em, strong, links, ...)
     *
     * @api
     *
     * @return array an array of \Prismic\Fragment\Span\SpanInterface objects that contain the formatting
     */
    public function getSpans()
    {
        return $this->spans;
    }

    /**
     * Returns the heading's level.
     *
     * @api
     *
     * @return string the heading's level.
     */
    public function isOrdered()
    {
        return $this->ordered;
    }
}
