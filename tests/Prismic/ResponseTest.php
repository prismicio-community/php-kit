<?php

declare(strict_types=1);

namespace Prismic\Test;

use Prismic\Document\Hydrator;
use Prismic\Response;

class ResponseTest extends TestCase
{

    public function testFromString()
    {
        $jsonString = $this->getJsonFixture('response-test.json');
        $hydrator = $this->prophesize(Hydrator::class);
        $hydrator->hydrate()->shouldNotBeCalled();

        $response = Response::fromJsonString($jsonString, $hydrator->reveal());

        $this->assertInternalType('integer', $response->getCurrentPageNumber());
        $this->assertInternalType('integer', $response->getResultsPerPage());
        $this->assertInternalType('integer', $response->getTotalResults());
        $this->assertInternalType('integer', $response->getTotalPageCount());
        $this->assertInternalType('array', $response->getResults());
        $this->assertInternalType('string', $response->getNextPageUrl());
        $this->assertNull($response->getPrevPageUrl());
    }

    /**
     * @expectedException \Prismic\Exception\InvalidArgumentException
     * @expectedExceptionMessage Failed to decode json payload
     */
    public function testFromJsonStringThrowsExceptionForInvalidJson()
    {
        $jsonString = 'invalid';
        $hydrator = $this->prophesize(Hydrator::class);
        $hydrator->hydrate()->shouldNotBeCalled();
        $response = Response::fromJsonString($jsonString, $hydrator->reveal());
    }
}
