<?php
declare(strict_types=1);

namespace Prismic\Exception;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\TransferException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\RequestInterface;

class RequestFailureException extends RuntimeException
{

    /**
     * @var TransferException|null
     */
    protected $guzzleException;

    /**
     * Factory to return a Prismic Exception wrapping a Guzzle Exception
     */
    public static function fromGuzzleException(GuzzleException $e) : self
    {
        if (method_exists($e, 'getRequest')) {
            return static::fromGuzzleRequestException($e);
        }
        $exception = new static('Api Request Failed', 500, $e);
        $exception->guzzleException = $e;
        return $exception;
    }

    /**
     * Factory to wrap a Guzzle Request Exception when we should have access to a request and a response
     */
    protected static function fromGuzzleRequestException(TransferException $e) : self
    {
        $response = method_exists($e, 'getResponse') ? $e->getResponse() : null;
        $code     = $response ? $response->getStatusCode() : 0;
        $reason   = $response ? $response->getReasonPhrase() : 'No Response';
        $request  = $e->getRequest();
        $url      = $request->getUri();

        $message = sprintf(
            'The %s request to the repository %s resulted in a %d %s error. Complete URL: %s',
            $request->getMethod(),
            $url->getHost(),
            $code,
            $reason,
            (string) $url
        );

        $exception = new static($message, $code, $e);
        $exception->guzzleException = $e;
        return $exception;
    }

    public function getResponse() :? ResponseInterface
    {
        if (! $this->guzzleException || ! method_exists($this->guzzleException, 'getResponse')) {
            return null;
        }
        return $this->guzzleException->getResponse();
    }

    public function getRequest() :? RequestInterface
    {
        if (! $this->guzzleException || ! method_exists($this->guzzleException, 'getRequest')) {
            return null;
        }
        return $this->guzzleException->getRequest();
    }
}
