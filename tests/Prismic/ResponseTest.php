<?php

declare(strict_types=1);

namespace Prismic\Test;

use Prismic\Document\Hydrator;
use Prismic\Exception\InvalidArgumentException;
use Prismic\Response;

class ResponseTest extends TestCase
{
    public function testFromString() : void
    {
        $jsonString = $this->getJsonFixture('response-test.json');
        $hydrator = $this->prophesize(Hydrator::class);
        $hydrator->hydrate()->shouldNotBeCalled();

        $response = Response::fromJsonString($jsonString, $hydrator->reveal());

        $this->assertIsInt($response->getCurrentPageNumber());
        $this->assertIsInt($response->getResultsPerPage());
        $this->assertIsInt($response->getTotalResults());
        $this->assertIsInt($response->getTotalPageCount());
        $this->assertIsArray($response->getResults());
        $this->assertIsString($response->getNextPageUrl());
        $this->assertNull($response->getPrevPageUrl());
    }

    public function testFromJsonStringThrowsExceptionForInvalidJson() : void
    {
        $jsonString = 'invalid';
        $hydrator = $this->prophesize(Hydrator::class);
        $hydrator->hydrate()->shouldNotBeCalled();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Failed to decode json payload');
        Response::fromJsonString($jsonString, $hydrator->reveal());
    }
}
