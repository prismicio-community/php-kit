<?php
declare(strict_types=1);

namespace Prismic;

/**
 * Interface Predicate
 *
 * @package Prismic
 */
interface Predicate
{
    public function q() : string;
}
