<?php

namespace Prismic\Fragment;

use Prismic\LinkResolver;

class Block implements BlockInterface
{
    use MagicTrait, AttributesTrait;

    public function render(string $content, LinkResolver $linkResolver = null, \closure $htmlSerializer = null): string
    {
        $attributes = $this->getAttributes();
//        $attributes[] = sprintf('x-data-type="%s"', $this->type);
        $attributes = implode(' ', $attributes);

        return nl2br(sprintf(
            '<%s%s>%s</%s>',
            self::HTML_TAG_MAPPING[$this->type] ?? self::HTML_TAG_DEFAULT,
            $attributes ? ' ' . $attributes : '',
            $content,
            self::HTML_TAG_MAPPING[$this->type] ?? self::HTML_TAG_DEFAULT,
        ));
    }
}
