<?php
declare(strict_types=1);

namespace Prismic;

use JsonException;
use Prismic\Exception\JsonError;
use TypeError;
use function json_decode;
use function json_encode;
use const JSON_THROW_ON_ERROR;

final class Json
{
    /**
     * @throws JsonError If decoding the payload fails for any reason.
     */
    public static function decodeObject(string $jsonString) : object
    {
        try {
            return json_decode($jsonString, false, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw JsonError::unserializeFailed($exception, $jsonString);
        } catch (TypeError $error) {
            throw JsonError::cannotUnserializeToObject($jsonString);
        }
    }

    /**
     * Decode a json string without enforcing the return type
     *
     * @return mixed
     *
     * @throws JsonError If decoding the payload fails for any reason.
     */
    public static function decode(string $jsonString, bool $asArray)
    {
        try {
            return json_decode($jsonString, $asArray, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw JsonError::unserializeFailed($exception, $jsonString);
        }
    }

    /**
     * @param mixed $value
     *
     * @throws JsonError If encoding the value fails for any reason.
     */
    public static function encode($value) : string
    {
        try {
            return json_encode($value, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw JsonError::serializeFailed($exception);
        }
    }
}
