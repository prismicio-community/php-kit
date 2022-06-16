<?php
declare(strict_types=1);

namespace Prismic\Fragment;

use Prismic\LinkResolver;

/**
 * This class embodies a group of RichText blocks.
 * This is only for internal use, when a RichText fragment gets rendered as HTML:
 * the first thing that happens is that RichText blocks get grouped together,
 * so that list-items can later be serialized with a <ul> before and a </ul> after, for instance.
 * This is the data structure that allows that temporary storage.
 */
class BlockGroup implements BlockInterface
{
    private const GROUP_TAG_MAPPING = [
        'list-item'     => 'ul',
        'o-list-item'   => 'ol',
    ];

    /**
     * @var BlockInterface[] the array of BlockInterface objects that are being grouped here
     */
    private array $blocks;

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
     * Tell if a new block may be added to this group
     *
     * @param BlockInterface $block
     * @return bool
     */
    public function match(BlockInterface $block): bool
    {
        $type = $this->blocks[0]->type;
        return isset(self::GROUP_TAG_MAPPING[$type]) && $type === $block->type;
    }

    public function render(string $content, LinkResolver $linkResolver = null, \closure $htmlSerializer = null): string
    {
        if (empty($this->blocks)) {
            return '';
        }

        $tag = self::GROUP_TAG_MAPPING[$this->blocks[0]->type ?? ''] ?? '';

        return ($tag ? "<$tag>" : '') . array_reduce(
            $this->blocks,
            static fn($carry, BlockInterface $block) =>
                $carry . $block->render($block->text ?? '', $linkResolver, $htmlSerializer),
            ''
        ) . ($tag ? "</$tag>" : '');
    }
}
