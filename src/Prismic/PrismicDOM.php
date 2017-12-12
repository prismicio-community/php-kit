<?php
/**
 * This file is part of the Prismic PHP SDK
 *
 * Copyright 2013 Zengularity (http://www.zengularity.com).
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Prismic;

use Prismic\RichText\RichText;

class PrismicDOM
{
    public static function asText($richText)
    {
        return RichText::asText($richText);
    }

    public static function asHtml($richText, $linkResolver = NULL, $htmlSerializer = NULL)
    {
        return RichText::asHtml($richText, $linkResolver, $htmlSerializer);
    }

    public static function asDate($date = NULL)
    {
        if (!$date) {
            return NULL;
        }
        
        return new \DateTime($date);
    }

    public static function getLinkUrl($link = NULL, $linkResolver = NULL)
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
