<?php

namespace Prismic\Dom;

class Link
{
    public static function asUrl($link = NULL, $linkResolver = NULL)
    {
        if (!$link) {
            return NULL;
        }

        if ($link->link_type === 'Document') {
            return $linkResolver ? $linkResolver($link) : NULL;
        }

        return $link->url;
    }
}
