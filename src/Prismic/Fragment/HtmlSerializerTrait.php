<?php

namespace Prismic\Fragment;

trait HtmlSerializerTrait
{
    public function renderHtmlSerializer(
        string $content,
        \closure $htmlSerializer = null
    ): string {
        if ($htmlSerializer && $custom = $htmlSerializer($this, $content)) {
            return $custom;
        }

        return '';
    }
}
