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

class Date
{
    public static function asDate($date = NULL)
    {
        if (!$date) {
            return NULL;
        }
        
        return new \DateTime($date);
    }
}
