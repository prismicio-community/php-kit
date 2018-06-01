<?php
declare(strict_types=1);

namespace Prismic\Test\Document;

use Prismic\Document\Hydrator;
use Prismic\DocumentInterface;
use Prismic\Test\TestCase;
use Prismic\Api;

class HydratorTest extends TestCase
{

    /**
     * @expectedException \Prismic\Exception\InvalidArgumentException
     */
    public function testExceptionThrownMappingTypeWhenClassDoesNotImplementCorrectInterface()
    {
        /** @var Api $api */
        $api = $this->prophesize(Api::class)->reveal();
        $hydrator = new Hydrator($api, [], DocumentInterface::class);

        $hydrator->mapType('whatever', \stdClass::class);
    }

}
