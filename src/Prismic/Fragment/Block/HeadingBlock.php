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
class HeadingBlock extends TextBlock
{
    /**
     * @var string the heading's level (currently, either 1, 2 or 3)
     */
    private $level;

    /**
     * Constructs an heading block.
     *
     * @param string $text  the unformatted text
     * @param array  $spans an array of \Prismic\Fragment\Span\SpanInterface objects that contain the formatting (em, strong, links, ...)
     * @param string $level the heading's level
     * @param string $label may be null
     */
    public function __construct($text, $spans, $level, $label = NULL)
    {
        $this->level = $level;
        parent::__construct($text, $spans, $label);
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
