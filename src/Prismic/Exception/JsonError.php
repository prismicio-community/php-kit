<?php
declare(strict_types=1);

namespace Prismic\Exception;

use JsonException;
use function sprintf;

class JsonError extends JsonException implements ExceptionInterface
{
    /** @var string|null */
    private $payload;

    public static function unserializeFailed(JsonException $exception, string $payload) : self
    {
        $error = new static(
            sprintf(
                'Failed to decode JSON payload: %s',
                $exception->getMessage()
            ),
            $exception->getCode(),
            $exception
        );

        $error->payload = $payload;

        return $error;
    }

    public static function serializeFailed(JsonException $exception) : self
    {
        return new static(
            sprintf(
                'Failed to encode the given data to a JSON string: %s',
                $exception->getMessage()
            ),
            $exception->getCode(),
            $exception
        );
    }

    public static function cannotUnserializeToObject(string $payload) : self
    {
        return new static(sprintf(
            'The given payload cannot be unserialized as an object: %s',
            $payload
        ));
    }

    public function payload() :? string
    {
        return $this->payload;
    }
}
