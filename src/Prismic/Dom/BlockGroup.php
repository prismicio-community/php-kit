<?php
declare(strict_types=1);

namespace Prismic\Dom;

use Prismic\Fragment\BlockInterface;

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
     * @var string|null the tag to use if should be (values are either "ul", "ol" or null)
     */
    private ?string $tag;
    /**
     * @var BlockInterface[] the array of BlockInterface objects that are being grouped here
     */
    private array $blocks;

    /**
     * Constructs a group of RichText blocks
     *
     * @param string|null $tag the tag to use if should be (values are either "ul", "ol" or null)
     * @param BlockInterface[] $blocks the array of BlockInterface objects that are being grouped here
     */
    public function __construct(
        ?string $tag,
        array $blocks = []
    ) {
        $this->tag = $tag;
        array_walk($blocks, [$this, 'addBlock']);
    }

    /**
     * Adds a block to the group of blocks.
     *
     * @param BlockInterface $block the block to add
     */
    public function addBlock(BlockInterface $block): void
    {
        $this->blocks[] = $block;
    }

    /**
     * Returns the tag to use if should be (values are either "ul", "ol" or null).
     *
     * @return string|null the tag to use if should be (values are either "ul", "ol" or null).
     */
    public function getTag(): ?string
    {
        return $this->tag;
    }

    /**
     * Returns the array of BlockInterface objects that are being grouped here
     *
     * @return BlockInterface[] the array of BlockInterface objects that are being grouped here
     */
    public function getBlocks(): array
    {
        return $this->blocks;
    }
}
