<?php
declare(strict_types=1);

namespace Prismic\Document\Fragment;

class Boolean extends AbstractScalarFragment
{
    public function asBoolean() : bool
    {
        return (bool) $this->value;
    }
}
