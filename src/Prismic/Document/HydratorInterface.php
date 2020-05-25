<?php
declare(strict_types=1);

namespace Prismic\Document;

use Prismic\DocumentInterface;

interface HydratorInterface
{
    public function hydrate(object $object) : DocumentInterface;
}
