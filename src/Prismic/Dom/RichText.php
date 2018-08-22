<?php

namespace Prismic\Dom;

use Prismic\Dom\BlockGroup;

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
     *
     * @param object $richText the rich text object
     *
     * @return string the text version of the RichText fragment
     */
    public static function asText($richText)
    {
        $result = '';

        foreach ($richText as $block) {
            if (isset($block->text)) {
                $result .= $block->text . "\n";
            }
        }

        return $result;
    }

    /**
     * Builds a HTML version of the RichText fragment
     *
     *
     * @param object                $richText       the rich text object
     * @param \Prismic\LinkResolver $linkResolver   the link resolver
     * @param lambda                $htmlSerializer an optional function to generate custom HTML code
     *
     * @return string the HTML version of the RichText fragment
     */
    public static function asHtml($richText, $linkResolver = null, $htmlSerializer = null)
    {
        $groups = [];

        foreach ($richText as $block) {
            $count = count($groups);
            if ($count > 0) {
                $lastOne = $groups[$count - 1];
                if ('ul' == $lastOne->getTag() && $block->type === 'list-item') {
                    $lastOne->addBlock($block);
                } elseif ('ol' == $lastOne->getTag() && $block->type === 'o-list-item') {
                    $lastOne->addBlock($block);
                } elseif ($block->type === 'list-item') {
                    $newBlockGroup = new BlockGroup('ul', []);
                    $newBlockGroup->addBlock($block);
                    array_push($groups, $newBlockGroup);
                } else {
                    if ($block->type === 'o-list-item') {
                        $newBlockGroup = new BlockGroup('ol', []);
                        $newBlockGroup->addBlock($block);
                        array_push($groups, $newBlockGroup);
                    } else {
                        $newBlockGroup = new BlockGroup(null, []);
                        $newBlockGroup->addBlock($block);
                        array_push($groups, $newBlockGroup);
                    }
                }
            } else {
                if ($block->type === 'list-item') {
                    $tag = 'ul';
                } elseif ($block->type === 'o-list-item') {
                    $tag = 'ol';
                } else {
                    $tag = null;
                }
                $newBlockGroup = new BlockGroup($tag, []);
                $newBlockGroup->addBlock($block);
                array_push($groups, $newBlockGroup);
            }
        }

        $html = '';

        foreach ($groups as $group) {
            $maybeTag = $group->getTag();
            if ($maybeTag) {
                $html = $html . '<' . $group->getTag() . '>';
                foreach ($group->getBlocks() as $block) {
                    $html = $html . RichText::asHtmlBlock($block, $linkResolver, $htmlSerializer);
                }
                $html = $html . '</' . $group->getTag() . '>';
            } else {
                foreach ($group->getBlocks() as $block) {
                    $html = $html . RichText::asHtmlBlock($block, $linkResolver, $htmlSerializer);
                }
            }
        }

        return $html;
    }

    /**
     * Transforms a block into HTML
     *
     *
     * @param object                $block          a given block
     * @param \Prismic\LinkResolver $linkResolver   the link resolver
     * @param lambda                $htmlSerializer the user's custom HTML serializer
     *
     * @return string the HTML representation of the block
     */
    private static function asHtmlBlock($block, $linkResolver = null, $htmlSerializer = null)
    {
        $content = '';

        if ($block->type === 'heading1' ||
            $block->type === 'heading2' ||
            $block->type === 'heading3' ||
            $block->type === 'heading4' ||
            $block->type === 'heading5' ||
            $block->type === 'heading6' ||
            $block->type === 'paragraph' ||
            $block->type === 'list-item' ||
            $block->type === 'o-list-item' ||
            $block->type === 'preformatted'
        ) {
            $content = RichText::insertSpans($block->text, $block->spans, $linkResolver, $htmlSerializer);
        }

        return RichText::serialize($block, $content, $linkResolver, $htmlSerializer);
    }

    /**
     * Transforms a text block into HTML
     *
     *
     * @param string                $text           the raw text of the block
     * @param array                 $spans          the spans of the block
     * @param \Prismic\LinkResolver $linkResolver   the link resolver
     * @param lambda                $htmlSerializer the user's custom HTML serializer
     *
     * @return string the HTML representation of the block
     */
    private static function insertSpans($text, array $spans, $linkResolver = null, $htmlSerializer = null)
    {
        if (empty($spans)) {
            return htmlentities($text, null, 'UTF-8');
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
            array_push($tagsStart[$span->start], $span);
            array_push($tagsEnd[$span->end], $span);
        }

        $c = null;
        $html = '';
        $stack = [];

        for ($pos = 0, $len = strlen($text) + 1; $pos < $len; $pos++) { // Looping to length + 1 to catch closing tags
            if (array_key_exists($pos, $tagsEnd)) {
                foreach ($tagsEnd[$pos] as $endTag) {
                    // Close a tag
                    $tag = array_pop($stack);
                    // Continue only if block contains content.
                    if ($tag && $tag['span']) {
                        $innerHtml = trim(
                            RichText::serialize($tag['span'], $tag['text'], $linkResolver, $htmlSerializer)
                        );
                        if (count($stack) == 0) {
                            // The tag was top level
                            $html .= $innerHtml;
                        } else {
                            // Add the content to the parent tag
                            $last = array_pop($stack);
                            $last['text'] = $last['text'] . $innerHtml;
                            array_push($stack, $last);
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
                    array_push($stack, [
                        'span' => $span,
                        'text' => ''
                    ]);
                }
            }
            if ($pos < strlen($text)) {
                $c = mb_substr($text, $pos, 1, 'UTF-8');
                if (count($stack) == 0) {
                    // Top-level text
                    $html .= htmlentities($c, null, 'UTF-8');
                } else {
                    // Inner text of a span
                    $last_idx = count($stack) - 1;
                    $last = $stack[$last_idx];
                    $stack[$last_idx] = [
                        'span' => $last['span'],
                        'text' => $last['text'] . htmlentities($c, null, 'UTF-8')
                    ];
                }
            }
        }

        return $html;
    }

    /**
     * Transforms an element into HTML
     *
     *
     * @param object                $element        element to serialize
     * @param string                $content        inner HTML content of the element
     * @param \Prismic\LinkResolver $linkResolver   the link resolver
     * @param lambda                $htmlSerializer the user's custom HTML serializer
     *
     * @return string the HTML representation of the element
     */
    private static function serialize($element, $content, $linkResolver, $htmlSerializer) : string
    {
        if ($htmlSerializer) {
            $custom = $htmlSerializer($element, $content);
            if ($custom) {
                return $custom;
            }
        }

        $classCode = '';

        if (isset($element->label)) {
            $classCode = ' class="' . $element->label . '"';
        }

        // Blocks
        switch ($element->type) {
            case 'heading1':
                return nl2br('<h1' . $classCode . '>' . $content . '</h1>');
            case 'heading2':
                return nl2br('<h2' . $classCode . '>' . $content . '</h2>');
            case 'heading3':
                return nl2br('<h3' . $classCode . '>' . $content . '</h3>');
            case 'heading4':
                return nl2br('<h4' . $classCode . '>' . $content . '</h4>');
            case 'heading5':
                return nl2br('<h5' . $classCode . '>' . $content . '</h5>');
            case 'heading6':
                return nl2br('<h6' . $classCode . '>' . $content . '</h6>');
            case 'paragraph':
                return nl2br('<p' . $classCode . '>' . $content . '</p>');
            case 'preformatted':
                return '<pre' . $classCode . '>' . $content . '</pre>';
            case 'list-item':
            case 'o-list-item':
                return nl2br('<li' . $classCode . '>' . $content . '</li>');
            case 'image':
                return (
                    '<p class="block-img' . (isset($element->label) ? (' ' . $element->label) : '') . '">' .
                        '<img src="' . $element->url . '" alt="' . htmlentities($element->alt) . '">' .
                    '</p>'
                );
            case 'embed':
                $providerAttr = '';
                if ($element->oembed->provider_name) {
                    $providerAttr = ' data-oembed-provider="' . strtolower($element->oembed->provider_name) . '"';
                }
                if ($element->oembed->html) {
                    return sprintf(
                        '<div data-oembed="%s" data-oembed-type="%s"%s>%s</div>',
                        $element->oembed->embed_url,
                        strtolower($element->oembed->type),
                        $providerAttr,
                        $element->oembed->html
                    );
                }
                return '';
        }

        // Spans
        $attributes = [];
        switch ($element->type) {
            case 'strong':
                $nodeName = 'strong';
                break;
            case 'em':
                $nodeName = 'em';
                break;
            case 'hyperlink':
                $nodeName = 'a';
                if (isset($element->data->target)) {
                    $attributes = array_merge([
                        'target' => $element->data->target,
                        'rel' => 'noopener',
                    ], $attributes);
                }
                if ($element->data->link_type === 'Document') {
                    $attributes['href'] = $linkResolver ? $linkResolver($element->data) : '';
                } else {
                    $attributes['href'] = $element->data->url;
                }
                if ($attributes['href'] === null) {
                    // We have no link (LinkResolver said it is not valid,
                    // or something else went wrong). Abort this span.
                    return $content;
                }
                break;
            default:
                // throw new \Exception("Unknown span type " . get_class($span));
                $nodeName = 'span';
        }

        if (isset($element->label)) {
            $attributes['class'] = $element->label;
        } elseif (isset($element->data->label)) {
            $attributes['class'] = $element->data->label;
        }

        $html = '<' . $nodeName;
        foreach ($attributes as $k => $v) {
            $html .= (' ' . $k . '="' . $v . '"');
        }
        $html .= ('>' . $content . '</' . $nodeName . '>');

        return $html;
    }
}
