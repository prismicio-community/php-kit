<?php

namespace Prismic\Dom;

use Prismic\Fragment\BlockGroup;
use Prismic\Fragment\Factory;
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

        $root = new BlockGroup;

        $group = null;
        foreach ($richText as $block) {
            $block = Factory::create($block);

            if (! $group || ! $group->match($block)) {
                $group = new BlockGroup;
                $root->addBlock($group);
            }

            $group->addBlock($block);
        }

        return $root->render('', $linkResolver, $htmlSerializer);
    }
}
