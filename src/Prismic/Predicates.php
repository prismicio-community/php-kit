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

use \Prismic\SimplePredicate;

class Predicates {

    public static function at($fragment, $value) {
        return new SimplePredicate("at", $fragment, [$value]);
    }

    public static function any($fragment, $values) {
        return new SimplePredicate("any", $fragment, [$values]);
    }

    public static function fulltext($fragment, $value) {
        return new SimplePredicate("fulltext", $fragment, [$value]);
    }

    public static function similar($documentId, $maxResults) {
        return new SimplePredicate("similar", $documentId, [$maxResults]);
    }

    public static function lt($fragment, $lowerBound) {
        return new SimplePredicate("number.lt", $fragment, [$lowerBound]);
    }

    public static function gt($fragment, $upperBound) {
        return new SimplePredicate("number.gt", $fragment, [$upperBound]);
    }

    public static function inRange($fragment, $lowerBound, $upperBound) {
        return new SimplePredicate("number.inRange", $fragment, [$lowerBound, $upperBound]);
    }

    public static function dateBefore($fragment, $before) {
        return new SimplePredicate("date.date-before", $fragment, [$before]);
    }

    public static function dateAfter($fragment, $after) {
        return new SimplePredicate("date.date-after", $fragment, [$after]);
    }

    public static function dateBetween($fragment, $before, $after) {
        return new SimplePredicate("date.date-between", $fragment, [$before, $after]);
    }

    public static function dayOfMonth($fragment, $day) {
        return new SimplePredicate("date.day-of-month", $fragment, [$day]);
    }

    public static function dayOfMonthBefore($fragment, $day) {
        return new SimplePredicate("date.day-of-month-before", $fragment, [$day]);
    }

    public static function dayOfMonthAfter($fragment, $day) {
        return new SimplePredicate("date.day-of-month-after", $fragment, [$day]);
    }

    public static function month($fragment, $month) {
        return new SimplePredicate("date.month", $fragment, [$month]);
    }

    public static function monthBefore($fragment, $month) {
        return new SimplePredicate("date.month-before", $fragment, [$month]);
    }

    public static function monthAfter($fragment, $month) {
        return new SimplePredicate("date.month-after", $fragment, [$month]);
    }

    public static function hour($fragment, $hour) {
        return new SimplePredicate("date.hour", $fragment, [$hour]);
    }

    public static function hourBefore($fragment, $hour) {
        return new SimplePredicate("date.hour-before", $fragment, [$hour]);
    }

    public static function hourAfter($fragment, $hour) {
        return new SimplePredicate("date.hour-after", $fragment, [$hour]);
    }

    public static function near($fragment, $latitude, $longitude, $radius) {
        return new SimplePredicate("geopoint.near", $fragment, [$latitude, $longitude, $radius]);
    }

}
