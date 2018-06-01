<?php

declare(strict_types=1);

namespace Prismic\Document;

use Prismic\Api;
use Prismic\DocumentInterface;
use Prismic\Exception\InvalidArgumentException;
use stdClass;
use function class_implements;
use function in_array;
use function sprintf;

class Hydrator implements HydratorInterface
{

    /** @var string */
    private $defaultClass;

    /** @var string[] */
    private $typeMap;

    /** @var Api */
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
        /** @var DocumentInterface $class */
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

    public function mapType(string $type, string $class) : void
    {
        $interfaces = class_implements($class);
        if (! in_array(DocumentInterface::class, $interfaces)) {
            throw new InvalidArgumentException(sprintf(
                'The class %s does not implement %s',
                $class,
                DocumentInterface::class
            ));
        }
        $this->typeMap[$type] = $class;
    }
}
