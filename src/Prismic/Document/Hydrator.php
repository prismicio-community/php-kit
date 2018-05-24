<?php

declare(strict_types=1);

namespace Prismic\Document;

use Prismic\Api;
use Prismic\DocumentInterface;
use stdClass;

class Hydrator implements HydratorInterface
{

    private $defaultClass;

    private $typeMap;

    private $api;

    public function __construct(Api $api, $typeMap, string $defaultClass)
    {
        $this->api = $api;
        $this->defaultClass = $defaultClass;
        $this->typeMap = $typeMap;
    }

    public function hydrate(stdClass $object) : DocumentInterface
    {
        /** @var string|null $type */
        $type = isset($object->type) ? $object->type : null;
        $class = $this->getClass($type);
        return $class::fromJsonObject($object, $this->api);
    }

    private function getClass(?string $type) : string
    {
        if ($type && isset($this->typeMap[$type])) {
            return $this->typeMap[$type];
        }
        return $this->defaultClass;
    }
}
