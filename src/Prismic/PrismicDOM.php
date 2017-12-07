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

use Prismic\Fragment\StructuredText;
use Prismic\Fragment\Link\DocumentLink;

class PrismicDOM
{
    public static function richText($richTextJson)
    {
        return StructuredText::parse($richTextJson);
    }

    public static function date($date = NULL)
    {
        if (!$date) {
            return NULL;
        }

        return new \DateTime($date);
    }

    public static function linkUrl($link = NULL, $linkResolver = NULL)
    {
        if (!$link) {
            return '';
        }

        if ($link->link_type === 'Document') {
            if (!$linkResolver) {
                return '';
            }
            return $linkResolver(DocumentLink::parse($link));
        }

        return $link->url ?: '';
    }
}
