<?php

namespace Prismic\Fragment;

use Prismic\LinkResolver;

/**
 * @property-read string $type
 * @property-read string $text
 *
 * @property-read array|null $spans
 * @property-read \stdClass|null $data
 *
 * @property-read string|null $label
 * @property-read string|null $url
 * @property-read string|null $alt
 */
interface BlockInterface
{
    public const HTML_TAG_MAPPING = [
            'list-item' => 'li',
            'o-list-item' => 'li',
            'heading1' => 'h1',
            'heading2' => 'h2',
            'heading3' => 'h3',
            'heading4' => 'h4',
            'heading5' => 'h5',
            'heading6' => 'h6',
            'paragraph' => 'p',
            'preformatted' => 'pre',
            'strong' => 'strong',
            'em' => 'em',
        ],
        HTML_TAG_DEFAULT = 'span';

    public function render(
        string $content,
        LinkResolver $linkResolver = null,
        \closure $htmlSerializer = null
    ): string;
}
