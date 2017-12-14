<?php

namespace Prismic\Dom;

class Link
{
    public static function asUrl($link = NULL, $linkResolver = NULL)
    {
        if (!$link) {
            return '';
        }

        if ($link->link_type === 'Document') {
            return $linkResolver ? $linkResolver($link) : '';
        }

        return $link->url ?: '';
    }
}
