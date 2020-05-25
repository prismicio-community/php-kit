<?php
declare(strict_types=1);

namespace Prismic\Test\Document;

use Prismic\Api;
use Prismic\Document\Hydrator;
use Prismic\DocumentInterface;
use Prismic\Exception\InvalidArgumentException;
use Prismic\Test\TestCase;
use stdClass;

class HydratorTest extends TestCase
{
    public function testExceptionThrownMappingTypeWhenClassDoesNotImplementCorrectInterface() : void
    {
        $api = $this->prophesize(Api::class)->reveal();
        $hydrator = new Hydrator($api, [], DocumentInterface::class);

        $this->expectException(InvalidArgumentException::class);
        $hydrator->mapType('whatever', stdClass::class);
    }

    public function testThatMapsGivenToTheConstructorWillBeValidated() : void
    {
        $api = $this->prophesize(Api::class)->reveal();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('does not implement');
        new Hydrator($api, ['whatever' => stdClass::class], DocumentInterface::class);
    }
}
