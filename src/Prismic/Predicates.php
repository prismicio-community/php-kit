<?php
declare(strict_types=1);

namespace Prismic;

use DateTime;

/**
 * A set of helpers to build predicates
 * @package Prismic
 */
class Predicates
{

    /**
     * @param string $fragment
     * @param string $value
     *
     * @return SimplePredicate
     */
    public static function at($fragment, $value)
    {
        return new SimplePredicate("at", $fragment, [$value]);
    }

    /**
     * @param string $fragment
     * @param string $value
     *
     * @return SimplePredicate
     */
    public static function not($fragment, $value)
    {
        return new SimplePredicate("not", $fragment, [$value]);
    }

    /**
     * @param string $fragment
     * @param string $values
     *
     * @return SimplePredicate
     */
    public static function any($fragment, $values)
    {
        return new SimplePredicate("any", $fragment, [$values]);
    }

    /**
     * @param string $fragment
     * @param string $values
     *
     * @return SimplePredicate
     */
    public static function in($fragment, $values)
    {
        return new SimplePredicate("in", $fragment, [$values]);
    }

    /**
     * @param string $fragment
     *
     * @return SimplePredicate
     */
    public static function has($fragment)
    {
        return new SimplePredicate("has", $fragment);
    }

    /**
     * @param string $fragment
     *
     * @return SimplePredicate
     */
    public static function missing($fragment)
    {
        return new SimplePredicate("missing", $fragment);
    }

    /**
     * @param string $fragment
     * @param string $value
     *
     * @return SimplePredicate
     */
    public static function fulltext($fragment, $value)
    {
        return new SimplePredicate("fulltext", $fragment, [$value]);
    }

    /**
     * @param string $documentId
     * @param int    $maxResults
     *
     * @return SimplePredicate
     */
    public static function similar($documentId, $maxResults)
    {
        return new SimplePredicate("similar", $documentId, [$maxResults]);
    }

    /**
     * @param string $fragment
     * @param int    $lowerBound
     *
     * @return SimplePredicate
     */
    public static function lt($fragment, $lowerBound)
    {
        return new SimplePredicate("number.lt", $fragment, [$lowerBound]);
    }

    /**
     * @param string $fragment
     * @param int    $upperBound
     *
     * @return SimplePredicate
     */
    public static function gt($fragment, $upperBound)
    {
        return new SimplePredicate("number.gt", $fragment, [$upperBound]);
    }

    /**
     * @param string $fragment
     * @param int    $lowerBound
     * @param int    $upperBound
     *
     * @return SimplePredicate
     */
    public static function inRange($fragment, $lowerBound, $upperBound)
    {
        return new SimplePredicate("number.inRange", $fragment, [$lowerBound, $upperBound]);
    }

    /**
     * @param string       $fragment
     * @param DateTime|int $before
     *
     * @return SimplePredicate
     */
    public static function dateBefore($fragment, $before)
    {
        if ($before instanceof DateTime) {
            $before = $before->getTimestamp() * 1000;
        }
        return new SimplePredicate("date.before", $fragment, [$before]);
    }

    /**
     * @param string       $fragment
     * @param DateTime|int $after
     *
     * @return SimplePredicate
     */
    public static function dateAfter($fragment, $after)
    {
        if ($after instanceof DateTime) {
            $after = $after->getTimestamp() * 1000;
        }
        return new SimplePredicate("date.after", $fragment, [$after]);
    }

    /**
     * @param string       $fragment
     * @param DateTime|int $before
     * @param DateTime|int $after
     *
     * @return SimplePredicate
     */
    public static function dateBetween($fragment, $before, $after)
    {
        if ($before instanceof DateTime) {
            $before = $before->getTimestamp() * 1000;
        }
        if ($after instanceof DateTime) {
            $after = $after->getTimestamp() * 1000;
        }
        return new SimplePredicate("date.between", $fragment, [$before, $after]);
    }

    /**
     * @param string $fragment
     * @param string $day
     *
     * @return SimplePredicate
     */
    public static function dayOfMonth($fragment, $day)
    {
        return new SimplePredicate("date.day-of-month", $fragment, [$day]);
    }

    /**
     * @param string $fragment
     * @param string $day
     *
     * @return SimplePredicate
     */
    public static function dayOfMonthBefore($fragment, $day)
    {
        return new SimplePredicate("date.day-of-month-before", $fragment, [$day]);
    }

    /**
     * @param string $fragment
     * @param string $day
     *
     * @return SimplePredicate
     */
    public static function dayOfMonthAfter($fragment, $day)
    {
        return new SimplePredicate("date.day-of-month-after", $fragment, [$day]);
    }

    /**
     * @param string $fragment
     * @param string $day
     *
     * @return SimplePredicate
     */
    public static function dayOfWeek($fragment, $day)
    {
        return new SimplePredicate("date.day-of-week", $fragment, [$day]);
    }

    /**
     * @param string $fragment
     * @param string $day
     *
     * @return SimplePredicate
     */
    public static function dayOfWeekBefore($fragment, $day)
    {
        return new SimplePredicate("date.day-of-week-before", $fragment, [$day]);
    }

    /**
     * @param string $fragment
     * @param string $day
     *
     * @return SimplePredicate
     */
    public static function dayOfWeekAfter($fragment, $day)
    {
        return new SimplePredicate("date.day-of-week-after", $fragment, [$day]);
    }

    /**
     * @param string $fragment
     * @param string $month
     *
     * @return SimplePredicate
     */
    public static function month($fragment, $month)
    {
        return new SimplePredicate("date.month", $fragment, [$month]);
    }

    /**
     * @param string $fragment
     * @param string $month
     *
     * @return SimplePredicate
     */
    public static function monthBefore($fragment, $month)
    {
        return new SimplePredicate("date.month-before", $fragment, [$month]);
    }

    /**
     * @param string $fragment
     * @param string $month
     *
     * @return SimplePredicate
     */
    public static function monthAfter($fragment, $month)
    {
        return new SimplePredicate("date.month-after", $fragment, [$month]);
    }

    /**
     * @param string $fragment
     * @param string $year
     *
     * @return SimplePredicate
     */
    public static function year($fragment, $year)
    {
        return new SimplePredicate("date.year", $fragment, [$year]);
    }

    /**
     * @param string $fragment
     * @param string $hour
     *
     * @return SimplePredicate
     */
    public static function hour($fragment, $hour)
    {
        return new SimplePredicate("date.hour", $fragment, [$hour]);
    }

    /**
     * @param string $fragment
     * @param string $hour
     *
     * @return SimplePredicate
     */
    public static function hourBefore($fragment, $hour)
    {
        return new SimplePredicate("date.hour-before", $fragment, [$hour]);
    }

    /**
     * @param string $fragment
     * @param string $hour
     *
     * @return SimplePredicate
     */
    public static function hourAfter($fragment, $hour)
    {
        return new SimplePredicate("date.hour-after", $fragment, [$hour]);
    }

    /**
     * @param string $fragment
     * @param string $latitude
     * @param string $longitude
     * @param string $radius
     *
     * @return SimplePredicate
     */
    public static function near($fragment, $latitude, $longitude, $radius)
    {
        return new SimplePredicate("geopoint.near", $fragment, [$latitude, $longitude, $radius]);
    }
}
