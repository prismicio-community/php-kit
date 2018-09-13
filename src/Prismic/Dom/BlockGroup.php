<?php
declare(strict_types=1);

namespace Prismic\Dom;

/**
 * This class embodies a group of RichText blocks.
 * This is only for internal use, when a RichText fragment gets rendered as HTML:
 * the first thing that happens is that RichText blocks get grouped together,
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
     * Constructs a group of RichText blocks
     *
     * @param string  $maybeTag  the tag to use if should be (values are either "ul", "ol" or null)
     * @param array   $blocks  the array of BlockInterface objects that are being grouped here
     */
    public function __construct(?string $maybeTag, array $blocks)
    {
        $this->maybeTag = $maybeTag;
        $this->blocks = $blocks;
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
    public function getTag() :? string
    {
        return $this->maybeTag;
    }

    /**
     * Returns the array of BlockInterface objects that are being grouped here
     *
     * @return array the array of BlockInterface objects that are being grouped here
     */
    public function getBlocks() : array
    {
        return $this->blocks;
    }
}
