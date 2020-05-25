<?php
declare(strict_types=1);

namespace Prismic;

use function implode;
use function is_array;
use function is_bool;
use function is_string;

class SimplePredicate implements Predicate
{
    /** @var string  */
    private $name;

    /** @var string  */
    private $fragment;

    /** @var mixed[] */
    private $args;

    /**
     * @param mixed[] $args
     */
    public function __construct(string $name, string $fragment, array $args = [])
    {
        $this->name     = $name;
        $this->fragment = $fragment;
        $this->args     = $args;
    }

    public function q() : string
    {
        $query = '[:d = ' . $this->name . '(';
        if ($this->name === 'similar') {
            $query .= '"' . $this->fragment . '"';
        } else {
            $query .= $this->fragment;
        }

        foreach ($this->args as $arg) {
            $query .= ', ' . $this->serializeField($arg);
        }

        $query .= ')]';

        return $query;
    }

    /**
     * @param mixed $value
     */
    private function serializeField($value) : string
    {
        if (is_string($value)) {
            return '"' . $value . '"';
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_array($value)) {
            $fields = [];
            foreach ($value as $elt) {
                $fields[] = $this->serializeField($elt);
            }

            return '[' . implode(', ', $fields) . ']';
        }

        return (string) $value;
    }
}
