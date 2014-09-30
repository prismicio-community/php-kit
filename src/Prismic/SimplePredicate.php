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

use Prismic\Predicate;

class SimplePredicate implements Predicate
{

    public function __construct($name, $fragment, $args)
    {
        $this->name = $name;
        $this->fragment = $fragment;
        $this->args = $args;
    }

    public function q()
    {
        $query = "[:d = " . $this->name . "(";
        if ($this->name == "similar") {
            $query .= "\"" . $this->fragment . "\"";
        } else {
            $query .= $this->fragment;
        }
        foreach ($this->args as $arg) {
            $query .= ", " . $this->serializeField($arg);
        }
        $query .= ")]";
        return $query;
    }

    private static function serializeField($value) {
        if (is_string($value)) {
            return "\"" . $value . "\"";
        }
        if (is_array($value)) {
            $str_array = array();
            foreach ($value as $elt) {
                array_push($str_array, SimplePredicate::serializeField($elt));
            }
            return "[" . join(", ", $str_array) . "]";
        }
        return (string)$value;
    }

}