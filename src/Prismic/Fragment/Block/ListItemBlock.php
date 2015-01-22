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
class ListItemBlock extends TextBlock
{
    /**
     * @var boolean true if part of an ordered list, false if unordered
     */
    private $ordered;

    /**
     * Constructs a list item block.
     *
     * @param string $text the unformatted text
     * @param array $spans an array of \Prismic\Fragment\Span\SpanInterface objects that contain the formatting (em, strong, links, ...)
     * @param boolean $ordered true if part of an ordered list, false if unordered
     * @param $label string
     */
    public function __construct($text, $spans, $ordered, $label = null)
    {
        $this->ordered = $ordered;
        parent::__construct($text, $spans, $label);
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
