<?php
declare(strict_types=1);

namespace Prismic\Document;

use Prismic\DocumentInterface;
use stdClass;

interface HydratorInterface
{
    public function hydrate(stdClass $object) : DocumentInterface;
}
