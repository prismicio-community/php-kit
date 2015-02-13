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

use DateTime;
use \Prismic\SimplePredicate;

/**
 * A set of helpers to build predicates
 * @package Prismic
 */
class Predicates {

    public static function at($fragment, $value) {
        return new SimplePredicate("at", $fragment, array($value));
    }

    public static function any($fragment, $values) {
        return new SimplePredicate("any", $fragment, array($values));
    }

    public static function in($fragment, $values) {
        return new SimplePredicate("in", $fragment, array($values));
    }

    public static function fulltext($fragment, $value) {
        return new SimplePredicate("fulltext", $fragment, array($value));
    }

    public static function similar($documentId, $maxResults) {
        return new SimplePredicate("similar", $documentId, array($maxResults));
    }

    public static function lt($fragment, $lowerBound) {
        return new SimplePredicate("number.lt", $fragment, array($lowerBound));
    }

    public static function gt($fragment, $upperBound) {
        return new SimplePredicate("number.gt", $fragment, array($upperBound));
    }

    public static function inRange($fragment, $lowerBound, $upperBound) {
        return new SimplePredicate("number.inRange", $fragment, array($lowerBound, $upperBound));
    }

    public static function dateBefore($fragment, $before) {
        if ($before instanceof DateTime) {
            $before = $before->getTimestamp() * 1000;
        }
        return new SimplePredicate("date.before", $fragment, array($before));
    }

    public static function dateAfter($fragment, $after) {
        if ($after instanceof DateTime) {
            $after = $after->getTimestamp() * 1000;
        }
        return new SimplePredicate("date.after", $fragment, array($after));
    }

    public static function dateBetween($fragment, $before, $after) {
        if ($before instanceof DateTime) {
            $before = $before->getTimestamp() * 1000;
        }
        if ($after instanceof DateTime) {
            $after = $after->getTimestamp() * 1000;
        }
        return new SimplePredicate("date.between", $fragment, array($before, $after));
    }

    public static function dayOfMonth($fragment, $day) {
        return new SimplePredicate("date.day-of-month", $fragment, array($day));
    }

    public static function dayOfMonthBefore($fragment, $day) {
        return new SimplePredicate("date.day-of-month-before", $fragment, array($day));
    }

    public static function dayOfMonthAfter($fragment, $day) {
        return new SimplePredicate("date.day-of-month-after", $fragment, array($day));
    }

    public static function dayOfWeek($fragment, $day) {
        return new SimplePredicate("date.day-of-week", $fragment, array($day));
    }

    public static function dayOfWeekBefore($fragment, $day) {
        return new SimplePredicate("date.day-of-week-before", $fragment, array($day));
    }

    public static function dayOfWeekAfter($fragment, $day) {
        return new SimplePredicate("date.day-of-week-after", $fragment, array($day));
    }

    public static function month($fragment, $month) {
        return new SimplePredicate("date.month", $fragment, array($month));
    }

    public static function monthBefore($fragment, $month) {
        return new SimplePredicate("date.month-before", $fragment, array($month));
    }

    public static function monthAfter($fragment, $month) {
        return new SimplePredicate("date.month-after", $fragment, array($month));
    }

    public static function year($fragment, $year) {
        return new SimplePredicate("date.year", $fragment, array($year));
    }

    public static function hour($fragment, $hour) {
        return new SimplePredicate("date.hour", $fragment, array($hour));
    }

    public static function hourBefore($fragment, $hour) {
        return new SimplePredicate("date.hour-before", $fragment, array($hour));
    }

    public static function hourAfter($fragment, $hour) {
        return new SimplePredicate("date.hour-after", $fragment, array($hour));
    }

    public static function near($fragment, $latitude, $longitude, $radius) {
        return new SimplePredicate("geopoint.near", $fragment, array($latitude, $longitude, $radius));
    }

}
