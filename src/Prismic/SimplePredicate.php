<?php
declare(strict_types=1);

namespace Prismic;

class SimplePredicate implements Predicate
{

    /** @var string  */
    private $name;

    /** @var string  */
    private $fragment;

    /** @var array  */
    private $args;

    /**
     * @param string $name
     * @param string $fragment
     * @param array  $args
     */
    public function __construct(string $name, string $fragment, array $args = [])
    {
        $this->name = $name;
        $this->fragment = $fragment;
        $this->args = $args;
    }

    /**
     * @return string
     */
    public function q() : string
    {
        $query = "[:d = " . $this->name . "(";
        if ($this->name === "similar") {
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
    private static function serializeField($value) : string
    {
        if (is_bool($value)) {
            return $value ? "true" : "false";
        }
        if (is_string($value)) {
            return "\"" . $value . "\"";
        }
        if (is_array($value)) {
            $str_array = [];
            foreach ($value as $elt) {
                array_push($str_array, static::serializeField($elt));
            }
            return "[" . implode(", ", $str_array) . "]";
        }
        return (string)$value;
    }
}
