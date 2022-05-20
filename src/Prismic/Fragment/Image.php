<?php

namespace Prismic\Fragment;

use Prismic\LinkResolver;

class Image implements BlockInterface
{
    use MagicTrait;

    public function render(string $content, LinkResolver $linkResolver = null, \closure $htmlSerializer = null): string
    {
        return sprintf(
            '<p class="block-img%s"><img src="%s" alt="%s" /></p>',
            isset($this->label) ? ' ' . $this->label : '',
            $this->url,
            htmlentities($this->alt)
        );
    }
}
