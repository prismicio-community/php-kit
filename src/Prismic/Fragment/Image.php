<?php

namespace Prismic\Fragment;

use Prismic\LinkResolver;

class Image implements BlockInterface
{
    use MagicTrait, HtmlSerializerTrait;

    public function render(string $content, LinkResolver $linkResolver = null, \closure $htmlSerializer = null): string
    {
        if ($result = $this->renderHtmlSerializer($content, $htmlSerializer)) {
            return $result;
        }

        return sprintf(
            '<p class="block-img%s"><img src="%s" alt="%s" /></p>',
            isset($this->label) ? ' ' . $this->label : '',
            $this->url,
            htmlentities($this->alt, ENT_NOQUOTES, 'UTF-8')
        );
    }
}
