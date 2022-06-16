<?php

namespace Prismic\Dom;

use Prismic\Fragment\Block;
use Prismic\Fragment\BlockInterface;
use Prismic\Fragment\Embed;
use Prismic\Fragment\Hyperlink;
use Prismic\Fragment\Image;
use Prismic\LinkResolver;

/**
 * This class embodies a RichText fragment.
 *
 * Technically, a RichText fragment is not much more than an array of blocks,
 * but there are many things to do with this fragment, including in the HTML serialization,
 * but not only. It is arguably the most powerful and manipulable way any CMS stores
 * structured text nowadays.
 */
class RichText
{

    private const
        GROUP_TAG_MAPPING = [
            'list-item' => 'ul',
            'o-list-item' => 'ol',
        ],
        DEFAULT_RENDERER = Block::class,
        RENDERERS = [
            'image' => Image::class,
            'embed' => Embed::class,
            'hyperlink' => Hyperlink::class,
        ];

    /**
     * Builds a text version of the RichText fragment
     *
     * @param array $richText the rich text object
     * @return string the text version of the RichText fragment
     */
    public static function asText(
        array $richText
    ): string {
        return array_reduce(
            $richText,
            static fn($result, $block) => $result . ($block->text ?? '') . "\n",
            ''
        );
    }

    /**
     * Builds a HTML version of the RichText fragment
     *
     * @param array $richText the rich text object
     * @param ?LinkResolver $linkResolver the link resolver
     * @param ?\closure $htmlSerializer an optional function to generate custom HTML code
     * @return string the HTML version of the RichText fragment
     */
    public static function asHtml(
        array $richText,
        LinkResolver $linkResolver = null,
        \closure $htmlSerializer = null
    ): string {
        $groups = [];
        $group = null;

        foreach ($richText as $block) {
            $block = self::createBlock($block);

            if (isset(self::GROUP_TAG_MAPPING[$block->type])) {
                // group item
                if (! $group || $group->getTag() !== self::GROUP_TAG_MAPPING[$block->type]) {
                    $group = new BlockGroup(self::GROUP_TAG_MAPPING[$block->type]);
                    // Add group to stack
                    $groups[] = $group;
                }
                // Add block to group
                $group->addBlock($block);
            } else {
                $groups[] = new BlockGroup(null, [$block]);
                $group = null;
            }
        }

        $html = '';

        /** @var BlockGroup $group */
        foreach ($groups as $group) {
            $groupTag = $group->getTag();
            if ($groupTag) {
                $html .= '<' . $groupTag . '>';
            }
            foreach ($group->getBlocks() as $block) {
                $html .= self::asHtmlBlock($block, $linkResolver, $htmlSerializer);
            }
            if ($groupTag) {
                $html .= '</' . $groupTag . '>';
            }
        }

        return $html;
    }

    /**
     * Transforms a block into HTML
     *
     *
     * @param BlockInterface $block a given block
     * @param LinkResolver|null $linkResolver the link resolver
     * @param \closure|null $htmlSerializer the user's custom HTML serializer
     *
     * @return string the HTML representation of the block
     */
    private static function asHtmlBlock(
        BlockInterface $block,
        LinkResolver $linkResolver = null,
        \closure $htmlSerializer = null
    ): string {
        $content = self::insertSpans($block->text ?? '', $block->spans ?? [], $linkResolver, $htmlSerializer);
        return self::serialize($block, $content, $linkResolver, $htmlSerializer);
    }

    /**
     * Transforms a text block into HTML
     *
     *
     * @param string $text the raw text of the block
     * @param array $spans the spans of the block
     * @param LinkResolver $linkResolver the link resolver
     * @param \closure $htmlSerializer the user's custom HTML serializer
     *
     * @return string the HTML representation of the block
     */
    private static function insertSpans(
        string $text,
        array $spans,
        LinkResolver $linkResolver = null,
        \closure $htmlSerializer = null
    ): string {
        if (empty($spans)) {
            return htmlentities($text, ENT_NOQUOTES, 'UTF-8');
        }

        $tagsStart = [];
        $tagsEnd = [];

        foreach ($spans as $span) {
            if (! array_key_exists($span->start, $tagsStart)) {
                $tagsStart[$span->start] = [];
            }
            if (! array_key_exists($span->end, $tagsEnd)) {
                $tagsEnd[$span->end] = [];
            }

            $tagsStart[$span->start][] = $span;
            $tagsEnd[$span->end][] = $span;
        }

        $html = '';
        $stack = [];

        for ($pos = 0, $len = mb_strlen($text) + 1; $pos < $len; $pos++) { // Looping to length + 1 to catch closing tags
            if (array_key_exists($pos, $tagsEnd)) {
                foreach ($tagsEnd[$pos] as $endTag) {
                    // Close a tag
                    $tag = array_pop($stack);
                    // Continue only if block contains content.
                    if ($tag && $tag['span']) {
                        $innerHtml = trim(
                            self::serialize(
                                self::createBlock($tag['span']),
                                $tag['text'],
                                $linkResolver,
                                $htmlSerializer
                            )
                        );
                        if (empty($stack)) {
                            // The tag was top level
                            $html .= $innerHtml;
                        } else {
                            // Add the content to the parent tag
                            $last = array_pop($stack);
                            $last['text'] .= $innerHtml;
                            $stack[] = $last;
                        }
                    }
                }
            }
            if (array_key_exists($pos, $tagsStart)) {
                // Sort bigger tags first to ensure the right tag hierarchy
                $sspans = $tagsStart[$pos];
                $spanSort = function ($a, $b) {
                    return ($b->end - $b->start) - ($a->end - $a->start);
                };
                usort($sspans, $spanSort);
                foreach ($sspans as $span) {
                    // Open a tag
                    $stack[] = [
                        'span' => $span,
                        'text' => ''
                    ];
                }
            }
            if ($pos < mb_strlen($text)) {
                $c = mb_substr($text, $pos, 1, 'UTF-8');
                if (empty($stack)) {
                    // Top-level text
                    $html .= htmlentities($c, ENT_NOQUOTES, 'UTF-8');
                } else {
                    // Inner text of a span
                    $last_idx = count($stack) - 1;
                    $stack[$last_idx]['text'] .= htmlentities($c, ENT_NOQUOTES, 'UTF-8');
                }
            }
        }

        return $html;
    }

    /**
     * Transforms an element into HTML
     *
     *
     * @param BlockInterface $element element to serialize
     * @param string $content inner HTML content of the element
     * @param LinkResolver $linkResolver the link resolver
     * @param \closure $htmlSerializer the user's custom HTML serializer
     *
     * @return string the HTML representation of the element
     */
    private static function serialize(
        BlockInterface $element,
        string $content,
        LinkResolver $linkResolver = null,
        \closure $htmlSerializer = null
    ): string {
        if ($htmlSerializer && ($custom = $htmlSerializer($element, $content))) {
            return $custom;
        }

        return $element->render(
            $content,
            $linkResolver,
            $htmlSerializer
        );
    }

    private static function createBlock(\stdClass $element): BlockInterface
    {
        /** @var BlockInterface $class */
        $class = self::RENDERERS[$element->type ?? ''] ?? self::DEFAULT_RENDERER;
        return new $class($element);
    }
}
