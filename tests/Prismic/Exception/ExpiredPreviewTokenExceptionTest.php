<?php
declare(strict_types=1);

namespace Prismic\Test\Exception;

use GuzzleHttp\Psr7\Response;
use Prismic\Exception\ExpiredPreviewTokenException;
use Prismic\Test\TestCase;

class ExpiredPreviewTokenExceptionTest extends TestCase
{
    public function testExceptionCanBeCreatedFromValidResponse() : void
    {
        $response = new Response();
        $response->getBody()->write('{"error":"Preview token expired"}');

        $exception = ExpiredPreviewTokenException::fromResponse($response);
        $this->assertInstanceOf(ExpiredPreviewTokenException::class, $exception);
        $this->assertSame($response, $exception->getResponse());
    }

    /** @return mixed[] */
    public function invalidPayloadBodyProvider() : iterable
    {
        yield ['Not Json'];
        yield ['{}'];
        yield ['{"error":"Something Else"}'];
        yield ['{"something":"Other Thing"}'];
        yield [''];
    }

    /**
     * @dataProvider invalidPayloadBodyProvider
     */
    public function testIsTokenExpiryResponseWithInvalidPayload(string $responseBody) : void
    {
        $response = new Response();
        $response->getBody()->write($responseBody);
        $this->assertFalse(ExpiredPreviewTokenException::isTokenExpiryResponse($response));
        $this->assertNull(ExpiredPreviewTokenException::fromResponse($response));
    }
}
