<?php
/**
 * This file is part of the Prismic PHP SDK
 *
 * Copyright 2013 Zengularity (http://www.zengularity.com).
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
