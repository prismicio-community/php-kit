<?php
declare(strict_types=1);

namespace Prismic;

use DateTimeInterface;
use Prismic\Exception\InvalidArgumentException;

/**
 * A set of helpers to build predicates
 */
class Predicates
{

    /**
     * @param string $fragment
     * @param string|array $value
     *
     * @return Predicate
     */
    public static function at(string $fragment, $value) : Predicate
    {
        return new SimplePredicate("at", $fragment, [$value]);
    }

    /**
     * @param string $fragment
     * @param string|array $value
     *
     * @return Predicate
     */
    public static function not(string $fragment, $value) : Predicate
    {
        return new SimplePredicate("not", $fragment, [$value]);
    }

    /**
     * @param string $fragment
     * @param array $values
     *
     * @return Predicate
     */
    public static function any(string $fragment, array $values) : Predicate
    {
        return new SimplePredicate("any", $fragment, [$values]);
    }

    /**
     * @param string $fragment
     * @param array $values
     *
     * @return Predicate
     */
    public static function in(string $fragment, array $values) : Predicate
    {
        return new SimplePredicate("in", $fragment, [$values]);
    }

    /**
     * @param string $fragment
     *
     * @return Predicate
     */
    public static function has(string $fragment) : Predicate
    {
        return new SimplePredicate("has", $fragment);
    }

    /**
     * @param string $fragment
     *
     * @return Predicate
     */
    public static function missing(string $fragment) : Predicate
    {
        return new SimplePredicate("missing", $fragment);
    }

    /**
     * @param string $fragment
     * @param string $value
     *
     * @return Predicate
     */
    public static function fulltext(string $fragment, string $value) : Predicate
    {
        return new SimplePredicate("fulltext", $fragment, [$value]);
    }

    /**
     * @param string $documentId
     * @param int    $maxResults
     *
     * @return Predicate
     */
    public static function similar(string $documentId, int $maxResults) : Predicate
    {
        return new SimplePredicate("similar", $documentId, [$maxResults]);
    }

    /**
     * @param string $fragment
     * @param int|float|string $lowerBound A number or numeric string
     *
     * @return Predicate
     */
    public static function lt(string $fragment, $lowerBound) : Predicate
    {
        if (! \is_numeric($lowerBound)) {
            throw new InvalidArgumentException(
                'Predicates::lt() expects a number as it’s second argument'
            );
        }
        return new SimplePredicate("number.lt", $fragment, [$lowerBound]);
    }

    /**
     * @param string $fragment
     * @param int|float|string $upperBound A number or numeric string
     *
     * @return Predicate
     */
    public static function gt(string $fragment, $upperBound) : Predicate
    {
        if (! \is_numeric($upperBound)) {
            throw new InvalidArgumentException(
                'Predicates::gt() expects a number as it’s second argument'
            );
        }
        return new SimplePredicate("number.gt", $fragment, [$upperBound]);
    }

    /**
     * @param string $fragment
     * @param int|float|string $lowerBound A number or numeric string
     * @param int|float|string $upperBound A number or numeric string
     *
     * @return Predicate
     */
    public static function inRange(string $fragment, $lowerBound, $upperBound) : Predicate
    {
        if (! \is_numeric($upperBound) || ! \is_numeric($lowerBound)) {
            throw new InvalidArgumentException(
                'Predicates::inRange() expects numbers for it’s second and third arguments'
            );
        }
        return new SimplePredicate("number.inRange", $fragment, [$lowerBound, $upperBound]);
    }

    /**
     * @param string $fragment
     * @param DateTimeInterface|int|string $before
     *
     * @return Predicate
     */
    public static function dateBefore(string $fragment, $before) : Predicate
    {
        if ($before instanceof DateTimeInterface) {
            $before = $before->getTimestamp() * 1000;
        }
        return new SimplePredicate("date.before", $fragment, [$before]);
    }

    /**
     * @param string $fragment
     * @param DateTimeInterface|int|string $after
     *
     * @return Predicate
     */
    public static function dateAfter(string $fragment, $after) : Predicate
    {
        if ($after instanceof DateTimeInterface) {
            $after = $after->getTimestamp() * 1000;
        }
        return new SimplePredicate("date.after", $fragment, [$after]);
    }

    /**
     * @param string $fragment
     * @param DateTimeInterface|int|string $before
     * @param DateTimeInterface|int|string $after
     *
     * @return Predicate
     */
    public static function dateBetween(string $fragment, $before, $after) : Predicate
    {
        if ($before instanceof DateTimeInterface) {
            $before = $before->getTimestamp() * 1000;
        }
        if ($after instanceof DateTimeInterface) {
            $after = $after->getTimestamp() * 1000;
        }
        return new SimplePredicate("date.between", $fragment, [$before, $after]);
    }

    /**
     * @param string $fragment
     * @param DateTimeInterface|int|string $day
     *
     * @return Predicate
     */
    public static function dayOfMonth(string $fragment, $day) : Predicate
    {
        if ($day instanceof DateTimeInterface) {
            $day = (int) $day->format('j');
        }
        return new SimplePredicate("date.day-of-month", $fragment, [$day]);
    }

    /**
     * @param string $fragment
     * @param DateTimeInterface|int|string $day
     *
     * @return Predicate
     */
    public static function dayOfMonthBefore(string $fragment, $day) : Predicate
    {
        if ($day instanceof DateTimeInterface) {
            $day = (int) $day->format('j');
        }
        return new SimplePredicate("date.day-of-month-before", $fragment, [$day]);
    }

    /**
     * @param string $fragment
     * @param DateTimeInterface|int|string $day
     *
     * @return Predicate
     */
    public static function dayOfMonthAfter(string $fragment, $day) : Predicate
    {
        if ($day instanceof DateTimeInterface) {
            $day = (int) $day->format('j');
        }
        return new SimplePredicate("date.day-of-month-after", $fragment, [$day]);
    }

    /**
     * @param string $fragment
     * @param DateTimeInterface|int|string $day
     *
     * @return Predicate
     */
    public static function dayOfWeek(string $fragment, $day) : Predicate
    {
        if ($day instanceof DateTimeInterface) {
            $day = (int) $day->format('N');
        }
        return new SimplePredicate("date.day-of-week", $fragment, [$day]);
    }

    /**
     * @param string $fragment
     * @param DateTimeInterface|int|string $day
     *
     * @return Predicate
     */
    public static function dayOfWeekBefore(string $fragment, $day) : Predicate
    {
        if ($day instanceof DateTimeInterface) {
            $day = (int) $day->format('N');
        }
        return new SimplePredicate("date.day-of-week-before", $fragment, [$day]);
    }

    /**
     * @param string $fragment
     * @param DateTimeInterface|int|string $day
     *
     * @return Predicate
     */
    public static function dayOfWeekAfter(string $fragment, $day) : Predicate
    {
        if ($day instanceof DateTimeInterface) {
            $day = (int) $day->format('N');
        }
        return new SimplePredicate("date.day-of-week-after", $fragment, [$day]);
    }

    /**
     * @param string $fragment
     * @param DateTimeInterface|int|string $month
     *
     * @return Predicate
     */
    public static function month(string $fragment, $month) : Predicate
    {
        if ($month instanceof DateTimeInterface) {
            $month = (int) $month->format('n');
        }
        return new SimplePredicate("date.month", $fragment, [$month]);
    }

    /**
     * @param string $fragment
     * @param DateTimeInterface|int|string $month
     *
     * @return Predicate
     */
    public static function monthBefore(string $fragment, $month) : Predicate
    {
        if ($month instanceof DateTimeInterface) {
            $month = (int) $month->format('n');
        }
        return new SimplePredicate("date.month-before", $fragment, [$month]);
    }

    /**
     * @param string $fragment
     * @param DateTimeInterface|int|string $month
     *
     * @return Predicate
     */
    public static function monthAfter(string $fragment, $month) : Predicate
    {
        if ($month instanceof DateTimeInterface) {
            $month = (int) $month->format('n');
        }
        return new SimplePredicate("date.month-after", $fragment, [$month]);
    }

    /**
     * @param string $fragment
     * @param DateTimeInterface|int|string $year
     *
     * @return Predicate
     */
    public static function year(string $fragment, $year) : Predicate
    {
        if ($year instanceof DateTimeInterface) {
            $year = (int) $year->format('Y');
        }
        return new SimplePredicate("date.year", $fragment, [$year]);
    }

    /**
     * @param string $fragment
     * @param DateTimeInterface|int|string $hour
     *
     * @return Predicate
     */
    public static function hour(string $fragment, $hour) : Predicate
    {
        if ($hour instanceof DateTimeInterface) {
            $hour = (int) $hour->format('H');
        }
        return new SimplePredicate("date.hour", $fragment, [$hour]);
    }

    /**
     * @param string $fragment
     * @param DateTimeInterface|int|string $hour
     *
     * @return Predicate
     */
    public static function hourBefore(string $fragment, $hour) : Predicate
    {
        if ($hour instanceof DateTimeInterface) {
            $hour = (int) $hour->format('H');
        }
        return new SimplePredicate("date.hour-before", $fragment, [$hour]);
    }

    /**
     * @param string $fragment
     * @param DateTimeInterface|int|string $hour
     *
     * @return Predicate
     */
    public static function hourAfter(string $fragment, $hour) : Predicate
    {
        if ($hour instanceof DateTimeInterface) {
            $hour = (int) $hour->format('H');
        }
        return new SimplePredicate("date.hour-after", $fragment, [$hour]);
    }

    /**
     * @param string $fragment
     * @param float $latitude
     * @param float $longitude
     * @param float $radius In Kilometers
     *
     * @return Predicate
     */
    public static function near(string $fragment, float $latitude, float $longitude, float $radius) : Predicate
    {
        return new SimplePredicate("geopoint.near", $fragment, [$latitude, $longitude, $radius]);
    }
}
