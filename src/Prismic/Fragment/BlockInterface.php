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
    public function render(
        string $content,
        LinkResolver $linkResolver = null,
        \closure $htmlSerializer = null
    ): string;
}
