<?php

namespace Prismic\Dom;

class Link
{
    /**
     * Returns the URL we're linking to.
     * The linkResolver will be needed in case the link is a document link.
     * Read more about the link resolver at https://prismic.io/docs/php/beyond-the-api/link-resolving
     *
     *
     * @param object                $link           The document link
     * @param \Prismic\LinkResolver $linkResolver   The link resolver
     *
     * @return string|null The URL of the resource we're linking to online
     */
    public static function asUrl($link, $linkResolver = null)
    {
        if ($link->link_type === 'Document') {
            return $linkResolver ? $linkResolver($link) : null;
        }

        return property_exists($link, 'url') ? $link->url : null;
    }
}
