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
     * @param string|array $value
     *
     * @return SimplePredicate
     */
    public static function at(string $fragment, $value) : SimplePredicate
    {
        return new SimplePredicate("at", $fragment, [$value]);
    }

    /**
     * @param string                      $fragment
     * @param string|array|float|DateTime $value
     *
     * @return SimplePredicate
     */
    public static function not(string $fragment, $value) : SimplePredicate
    {
        return new SimplePredicate("not", $fragment, [$value]);
    }

    /**
     * @param string $fragment
     * @param string|array $values
     *
     * @return SimplePredicate
     */
    public static function any(string $fragment, $values) : SimplePredicate
    {
        return new SimplePredicate("any", $fragment, [$values]);
    }

    /**
     * @param string $fragment
     * @param array $values
     *
     * @return SimplePredicate
     */
    public static function in(string $fragment, array $values) : SimplePredicate
    {
        return new SimplePredicate("in", $fragment, [$values]);
    }

    /**
     * @param string $fragment
     *
     * @return SimplePredicate
     */
    public static function has(string $fragment) : SimplePredicate
    {
        return new SimplePredicate("has", $fragment);
    }

    /**
     * @param string $fragment
     *
     * @return SimplePredicate
     */
    public static function missing(string $fragment) : SimplePredicate
    {
        return new SimplePredicate("missing", $fragment);
    }

    /**
     * @param string $fragment
     * @param string $value
     *
     * @return SimplePredicate
     */
    public static function fulltext(string $fragment, string $value) : SimplePredicate
    {
        return new SimplePredicate("fulltext", $fragment, [$value]);
    }

    /**
     * @param string $documentId
     * @param int    $maxResults
     *
     * @return SimplePredicate
     */
    public static function similar(string $documentId, int $maxResults) : SimplePredicate
    {
        return new SimplePredicate("similar", $documentId, [$maxResults]);
    }

    /**
     * @param string $fragment
     * @param float  $lowerBound
     *
     * @return SimplePredicate
     */
    public static function lt(string $fragment, float $lowerBound) : SimplePredicate
    {
        return new SimplePredicate("number.lt", $fragment, [$lowerBound]);
    }

    /**
     * @param string $fragment
     * @param float  $upperBound
     *
     * @return SimplePredicate
     */
    public static function gt(string $fragment, float $upperBound) : SimplePredicate
    {
        return new SimplePredicate("number.gt", $fragment, [$upperBound]);
    }

    /**
     * @param string $fragment
     * @param float    $lowerBound
     * @param float    $upperBound
     *
     * @return SimplePredicate
     */
    public static function inRange(string $fragment, float $lowerBound, float $upperBound) : SimplePredicate
    {
        return new SimplePredicate("number.inRange", $fragment, [$lowerBound, $upperBound]);
    }

    /**
     * @param string          $fragment
     * @param DateTime|string $before
     *
     * @return SimplePredicate
     */
    public static function dateBefore(string $fragment, $before) : SimplePredicate
    {
        if ($before instanceof DateTime) {
            $before = $before->getTimestamp() * 1000;
        }
        return new SimplePredicate("date.before", $fragment, [$before]);
    }

    /**
     * @param string          $fragment
     * @param DateTime|string $after
     *
     * @return SimplePredicate
     */
    public static function dateAfter(string $fragment, $after) : SimplePredicate
    {
        if ($after instanceof DateTime) {
            $after = $after->getTimestamp() * 1000;
        }
        return new SimplePredicate("date.after", $fragment, [$after]);
    }

    /**
     * @param string          $fragment
     * @param DateTime|string $before
     * @param DateTime|string $after
     *
     * @return SimplePredicate
     */
    public static function dateBetween(string $fragment, $before, $after) : SimplePredicate
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
     * @param int    $day
     *
     * @return SimplePredicate
     */
    public static function dayOfMonth(string $fragment, int $day) : SimplePredicate
    {
        return new SimplePredicate("date.day-of-month", $fragment, [$day]);
    }

    /**
     * @param string $fragment
     * @param int    $day
     *
     * @return SimplePredicate
     */
    public static function dayOfMonthBefore(string $fragment, int $day) : SimplePredicate
    {
        return new SimplePredicate("date.day-of-month-before", $fragment, [$day]);
    }

    /**
     * @param string $fragment
     * @param int    $day
     *
     * @return SimplePredicate
     */
    public static function dayOfMonthAfter(string $fragment, int $day) : SimplePredicate
    {
        return new SimplePredicate("date.day-of-month-after", $fragment, [$day]);
    }

    /**
     * @param string     $fragment
     * @param string|int $day
     *
     * @return SimplePredicate
     */
    public static function dayOfWeek(string $fragment, $day) : SimplePredicate
    {
        return new SimplePredicate("date.day-of-week", $fragment, [$day]);
    }

    /**
     * @param string     $fragment
     * @param string|int $day
     *
     * @return SimplePredicate
     */
    public static function dayOfWeekBefore(string $fragment, $day) : SimplePredicate
    {
        return new SimplePredicate("date.day-of-week-before", $fragment, [$day]);
    }

    /**
     * @param string     $fragment
     * @param string|int $day
     *
     * @return SimplePredicate
     */
    public static function dayOfWeekAfter(string $fragment, $day) : SimplePredicate
    {
        return new SimplePredicate("date.day-of-week-after", $fragment, [$day]);
    }

    /**
     * @param string     $fragment
     * @param string|int $month
     *
     * @return SimplePredicate
     */
    public static function month(string $fragment, $month) : SimplePredicate
    {
        return new SimplePredicate("date.month", $fragment, [$month]);
    }

    /**
     * @param string     $fragment
     * @param string|int $month
     *
     * @return SimplePredicate
     */
    public static function monthBefore(string $fragment, $month) : SimplePredicate
    {
        return new SimplePredicate("date.month-before", $fragment, [$month]);
    }

    /**
     * @param string     $fragment
     * @param string|int $month
     *
     * @return SimplePredicate
     */
    public static function monthAfter(string $fragment, $month) : SimplePredicate
    {
        return new SimplePredicate("date.month-after", $fragment, [$month]);
    }

    /**
     * @param string $fragment
     * @param int    $year
     *
     * @return SimplePredicate
     */
    public static function year(string $fragment, int $year) : SimplePredicate
    {
        return new SimplePredicate("date.year", $fragment, [$year]);
    }

    /**
     * @param string $fragment
     * @param int    $hour
     *
     * @return SimplePredicate
     */
    public static function hour(string $fragment, int $hour) : SimplePredicate
    {
        return new SimplePredicate("date.hour", $fragment, [$hour]);
    }

    /**
     * @param string $fragment
     * @param int    $hour
     *
     * @return SimplePredicate
     */
    public static function hourBefore(string $fragment, int $hour) : SimplePredicate
    {
        return new SimplePredicate("date.hour-before", $fragment, [$hour]);
    }

    /**
     * @param string $fragment
     * @param int    $hour
     *
     * @return SimplePredicate
     */
    public static function hourAfter(string $fragment, int $hour) : SimplePredicate
    {
        return new SimplePredicate("date.hour-after", $fragment, [$hour]);
    }

    /**
     * @param string $fragment
     * @param float  $latitude
     * @param float  $longitude
     * @param float  $radius
     *
     * @return SimplePredicate
     */
    public static function near(string $fragment, float $latitude, float $longitude, float $radius) : SimplePredicate
    {
        return new SimplePredicate("geopoint.near", $fragment, [$latitude, $longitude, $radius]);
    }
}
