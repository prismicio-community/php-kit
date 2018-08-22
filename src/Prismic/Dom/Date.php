<?php
declare(strict_types=1);

namespace Prismic\Dom;

class Date
{
    /**
     * Returns the date as a DateTime object
     *
     *
     * @param string $date The date as a string
     *
     * @return \DateTime The DateTime object representing the date
     */
    public static function asDate($date)
    {
        return new \DateTime($date, new \DateTimeZone('UTC'));
    }
}
