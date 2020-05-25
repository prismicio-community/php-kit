<?php
declare(strict_types=1);

namespace Prismic;

interface Predicate
{
    public function q() : string;
}
