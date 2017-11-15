<?php
/**
 * This file is part of the Prismic PHP SDK
 *
 * Copyright 2013 Zengularity (http://www.zengularity.com).
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Prismic\Fragment;

/**
 * This class embodies a group of StructuredText blocks.
 * This is only for internal use, when a StructuredText fragment gets rendered as HTML:
 * the first thing that happens is that StructuredText blocks get grouped together,
 * so that list-items can later be serialized with a <ul> before and a </ul> after, for instance.
 * This is the data structure that allows that temporary storage.
 */
class BlockGroup
{
    /**
     * @var string the tag to use if should be (values are either "ul", "ol" or null)
     */
    private $maybeTag;
    /**
     * @var array the array of BlockInterface objects that are being grouped here
     */
    private $blocks;

    /**
     * Constructs a group of StructuredText blocks
     *
     * @param string  $maybeTag  the tag to use if should be (values are either "ul", "ol" or null)
     * @param array   $blocks  the array of BlockInterface objects that are being grouped here
     */
    public function __construct($maybeTag, $blocks)
    {
        $this->maybeTag = $maybeTag;
        $this->blocks = $blocks;
    }

    /**
     * Supposedly, should render the group as text; really, returns null, as
     * there's no reason to render it as text.
     *
     * @return string null in all cases
     */
    public function asText()
    {
        return null;
    }

    /**
     * Supposedly, should render the group as HTML; really, returns null, as
     * there's no reason to render it as HTML.
     *
     * @param \Prismic\LinkResolver  $linkResolver  a link resolver
     *
     * @return string null in all cases
     */
    public function asHtml($linkResolver = null)
    {
        return null;
    }

    /**
     * Adds a block to the group of blocks.
     *
     * @param \Prismic\Fragment\Block\BlockInterface  $block  the block to add
     */
    public function addBlock($block)
    {
        array_push($this->blocks, $block);
    }

    /**
     * Returns the tag to use if should be (values are either "ul", "ol" or null).
     *
     * @return  string  the tag to use if should be (values are either "ul", "ol" or null).
     */
    public function getTag()
    {
        return $this->maybeTag;
    }

    /**
     * Returns the array of BlockInterface objects that are being grouped here
     *
     * @return array the array of BlockInterface objects that are being grouped here
     */
    public function getBlocks()
    {
        return $this->blocks;
    }
}
