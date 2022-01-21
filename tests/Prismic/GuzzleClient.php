<?php
declare(strict_types=1);

namespace Prismic\Test;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;

/**
 * GuzzleHttp\Client does not provide concrete get(), post() methods
 * as they proxy to __call, this stub exists purely to stop prophecy
 * complaining about missing methods
 */

class GuzzleClient extends Client
{

    public function get($uri, array $options = []): ResponseInterface
    {
        return new Response();
    }
}
