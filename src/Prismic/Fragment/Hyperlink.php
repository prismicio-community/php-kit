<?php

namespace Prismic\Fragment;

use Prismic\LinkResolver;

class Hyperlink implements BlockInterface
{
    use MagicTrait, AttributesTrait;

    public function render(string $content, LinkResolver $linkResolver = null, \closure $htmlSerializer = null): string
    {
        $attributes = $this->getAttributes();
        if (isset($this->data->target)) {
            $attributes[] = sprintf('target="%s"', $this->data->target);
            $attributes[] = 'rel="noopener"';
        }

        if ($this->data->link_type === 'Document') {
            $attributes[] = sprintf('href="%s"', $linkResolver ? $linkResolver($this->data) : '');
        } elseif (! isset($this->data->url)) {
            return '';
        } else {
            $attributes[] = sprintf('href="%s"', $this->data->url);
        }

        return sprintf(
            '<a %s>%s</a>',
            implode(' ', $attributes),
            $content
        );
    }
}
