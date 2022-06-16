<?php

namespace Prismic\Fragment;

use Prismic\LinkResolver;

class Block implements BlockInterface
{
    use MagicTrait,
        AttributesTrait,
        HtmlSerializerTrait,
        SpansTrait;

    public const HTML_TAG_MAPPING = [
            'list-item'     => 'li',
            'o-list-item'   => 'li',
            'heading1'      => 'h1',
            'heading2'      => 'h2',
            'heading3'      => 'h3',
            'heading4'      => 'h4',
            'heading5'      => 'h5',
            'heading6'      => 'h6',
            'paragraph'     => 'p',
            'preformatted'  => 'pre',
            'strong'        => 'strong',
            'em'            => 'em',
        ],
        HTML_TAG_DEFAULT    = 'span';

    public function render(string $content, LinkResolver $linkResolver = null, \closure $htmlSerializer = null): string
    {
        if ($result = $this->renderHtmlSerializer($content, $htmlSerializer)) {
            return $result;
        }

        $content = $this->renderSpans($content, $this->spans ?? [], $linkResolver, $htmlSerializer);

        $attributes = $this->getAttributes();
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
