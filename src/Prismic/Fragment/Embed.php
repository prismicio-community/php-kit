<?php

namespace Prismic\Fragment;

use Prismic\LinkResolver;

class Embed implements BlockInterface
{
    use MagicTrait;

    public function render(string $content, LinkResolver $linkResolver = null, \closure $htmlSerializer = null): string
    {
        if (! isset($this->oembed->html)) {
            return '';
        }

        $providerAttr = '';
        if (isset($this->oembed->provider_name)) {
            $providerAttr = ' data-oembed-provider="' . strtolower($this->oembed->provider_name) . '"';
        }

        return sprintf(
            '<div data-oembed="%s" data-oembed-type="%s"%s>%s</div>',
            $this->oembed->embed_url,
            strtolower($this->oembed->type),
            $providerAttr,
            $this->oembed->html
        );
    }
}
