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
 * This class embodies a heading (title) block inside a StructuredText fragment.
 */
class HeadingBlock implements TextInterface
{
    /**
     * @var string the unformatted text of the heading
     */
    private $text;
    /**
     * @var array an array of \Prismic\Fragment\Span\SpanInterface objects that contain the formatting (em, strong, links, ...)
     */
    private $spans;
    /**
     * @var string the heading's level (currently, either 1, 2 or 3)
     */
    private $level;


    /**
     * Constructs an heading block.
     *
     * @param string  $text   the unformatted text
     * @param array   $spans  an array of \Prismic\Fragment\Span\SpanInterface objects that contain the formatting (em, strong, links, ...)
     * @param string  $level  the heading's level
     */
    public function __construct($text, $spans, $level)
    {
        $this->text = $text;
        $this->spans = $spans;
        $this->level = $level;
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
    public function getLevel()
    {
        return $this->level;
    }
}
