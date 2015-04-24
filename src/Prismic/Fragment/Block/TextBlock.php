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
 * This interface embodies any block of a StructuredText fragment that contains text.
 * Its known implementations are HeadingBlock, ListItemBlock, ParagraphBlock, and PreformattedBlock.
 */
abstract class TextBlock implements BlockInterface
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
     * @var string the label (optional, may be null)
     */
    private $label;

    /**
     * Constructs a paragraph block.
     *
     * @param string $text the unformatted text
     * @param array $spans an array of \Prismic\Fragment\Span\SpanInterface objects that contain the formatting (em, strong, links, ...)
     * @param $label string
     */
    public function __construct($text, $spans, $label = NULL)
    {
        $this->text = $text;
        $this->spans = $spans ? $spans : array();
        $this->label = $label;
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
