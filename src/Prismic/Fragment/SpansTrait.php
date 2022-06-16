<?php

namespace Prismic\Fragment;

use Prismic\LinkResolver;

trait SpansTrait
{

    /**
     * Transforms a text block into HTML
     *
     *
     * @param string $text the raw text of the block
     * @param array $spans the spans of the block
     * @param LinkResolver|null $linkResolver the link resolver
     * @param \closure|null $htmlSerializer the user's custom HTML serializer
     *
     * @return string the HTML representation of the block
     */
    public function renderSpans(
        string $text,
        array $spans,
        LinkResolver $linkResolver = null,
        \closure $htmlSerializer = null
    ): string {
        if (empty($spans)) {
            return $text;
        }

        $stack = [
            ['text' => '']
        ];
        $stackIndex = 0;
        $tagsStart = [];
        $tagsEnd = [];

        foreach ($spans as $span) {
            if (! isset($tagsStart[$span->start])) {
                $tagsStart[$span->start] = [];
            }
            if (! isset($tagsEnd[$span->end])) {
                $tagsEnd[$span->end] = [];
            }

            $tagsStart[$span->start][] = $span;
            $tagsEnd[$span->end][] = $span;
        }

        // Sort bigger start tags first to ensure the right tag hierarchy
        array_walk(
            $tagsStart,
            static fn(&$tags) => usort(
                $tags,
                static fn($a, $b) => ($b->end - $b->start) - ($a->end - $a->start)
            )
        );

        // Looping to length + 1 to catch closing tags
        for ($pos = 0, $len = mb_strlen($text) + 1; $pos < $len; $pos++) {
            if (isset($tagsEnd[$pos])) {
                while (array_pop($tagsEnd[$pos])) {
                    // Close a tag
                    $tag = array_pop($stack);
                    $stackIndex--;

                    $innerHtml = $tag['span']->render(
                        $tag['text'],
                        $linkResolver,
                        $htmlSerializer
                    );

                    // Add the content to the parent tag
                    $stack[$stackIndex]['text'] .= $innerHtml;
                }
            }

            if (isset($tagsStart[$pos])) {
                foreach ($tagsStart[$pos] as $span) {
                    // Open a tag
                    $stack[] = [
                        'span' => Factory::create($span),
                        'text' => ''
                    ];
                    $stackIndex++;
                }
            }

            $stack[$stackIndex]['text'] .= htmlentities(
                mb_substr($text, $pos, 1, 'UTF-8'),
                ENT_NOQUOTES,
                'UTF-8'
            );
        }

        // return root stack
        return $stack[0]['text'] ?? '';
    }
}
