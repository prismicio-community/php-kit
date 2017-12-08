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

class PrismicDOM
{
    public static function getStructuredText($richText)
    {
        return StructuredText::parse($richText);
    }

    public static function asText($richText)
    {
        $result = '';

        foreach ($richText as $block) {
            $result .= $block->text . "\n";
        }

        return $result;
    }

    public static function asHtml($richText, $linkResolver = NULL)
    {
        return 'TODO asHtml';
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
