<?php
declare(strict_types=1);

namespace Prismic\Exception;

use Psr\Http\Message\ResponseInterface;
use function json_decode;
use function strtolower;

class ExpiredPreviewTokenException extends RuntimeException
{
    /**
     * This is the string we match in the API response to see if the error is for an expired token
     * @var string
     */
    private const RESPONSE_MESSAGE = 'Preview token expired';

    /** @var ResponseInterface|null */
    private $response;

    public static function isTokenExpiryResponse(ResponseInterface $response) : bool
    {
        $body = json_decode((string) $response->getBody(), true);
        if (! $body) {
            return false;
        }
        if (! isset($body['error'])) {
            return false;
        }
        return strtolower($body['error']) === strtolower(self::RESPONSE_MESSAGE);
    }

    public static function fromResponse(ResponseInterface $response) :? self
    {
        if (self::isTokenExpiryResponse($response)) {
            $exception = new self(
                'You are trying to initialise a preview with an expired token. '
                . 'Typically this is caused by following an out of date URL. '
                . 'Initiate the preview from the CMS to retrieve a fresh token',
                410 // Corresponds to 'Gone' status code
            );
            $exception->response = $response;
            return $exception;
        }
        return null;
    }

    public function getResponse() :? ResponseInterface
    {
        return $this->response;
    }
}
