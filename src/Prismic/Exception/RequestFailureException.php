<?php
declare(strict_types=1);

namespace Prismic\Exception;

use GuzzleHttp\Exception\GuzzleException;

class RequestFailureException extends RuntimeException
{

    /**
     * @param GuzzleException $e
     * @return static
     */
    public static function fromGuzzleException(GuzzleException $e) : self
    {
        return new static('Api Request Failed', 500, $e);
    }
}
