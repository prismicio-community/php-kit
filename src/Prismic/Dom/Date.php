<?php

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
