<?php
declare(strict_types=1);

namespace Prismic\Document;

use Prismic\Api;
use Prismic\DocumentInterface;
use Prismic\Exception\InvalidArgumentException;
use function assert;
use function class_implements;
use function in_array;
use function is_a;
use function is_string;
use function sprintf;

class Hydrator implements HydratorInterface
{
    /** @var string */
    private $defaultClass;

    /** @var string[] */
    private $typeMap;

    /** @var Api */
    private $api;

    /**
     * @param string[] $typeMap
     */
    public function __construct(Api $api, iterable $typeMap, string $defaultClass)
    {
        $this->api = $api;
        $this->defaultClass = $defaultClass;
        $this->typeMap = [];

        foreach ($typeMap as $key => $value) {
            $this->mapType($key, $value);
        }
    }

    public function hydrate(object $object) : DocumentInterface
    {
        $type = isset($object->type) && is_string($object->type) ? $object->type : null;
        $class = $this->getClass($type);
        assert(is_a($class, DocumentInterface::class, true));

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
        if (! in_array(DocumentInterface::class, $interfaces, true)) {
            throw new InvalidArgumentException(sprintf(
                'The class %s does not implement %s',
                $class,
                DocumentInterface::class
            ));
        }

        $this->typeMap[$type] = $class;
    }
}
