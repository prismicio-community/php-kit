<?php

namespace Prismic;

use Prismic\Predicate;

/**
 * Class SimplePredicate
 *
 * @package Prismic
 */
class SimplePredicate implements Predicate
{

    /**
     * @param string $name
     * @param string $fragment
     * @param array  $args
     */
    public function __construct($name, $fragment, array $args = array())
    {
        $this->name = $name;
        $this->fragment = $fragment;
        $this->args = $args;
    }

    /**
     * @return string
     */
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

    /**
     * @param mixed $value
     *
     * @return string
     */
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
