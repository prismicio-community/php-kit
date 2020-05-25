<?php
declare(strict_types=1);

namespace Prismic\Document\Fragment;

/**
 * Numbers are always returned as floats from the API, so assuming we have a numeric value, it should always be a float
 */

class Number extends AbstractScalarFragment
{
}
