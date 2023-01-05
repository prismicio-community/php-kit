<?php

declare(strict_types=1);

namespace Prismic\Exception;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/** @final This class is not designed to be open to extension */
class RequestFailureException extends RuntimeException
{

    /**
     * @var GuzzleException|null
     */
    protected $guzzleException;

    /**
     * Factory to return a Prismic Exception wrapping a Guzzle Exception
     *
     * @param GuzzleException $e
     *
     * @return self
     */
    public static function fromGuzzleException(GuzzleException $e): self
    {
        if ($e instanceof RequestException) {
            return static::fromGuzzleRequestException($e);
        }

        if ($e instanceof ConnectException) {
            return static::fromGuzzleRequestOrConnectException($e);
        }

        $exception = new static('Api Request Failed', 500, $e);
        $exception->guzzleException = $e;
        return $exception;
    }


    /**
     * Factory to wrap a Guzzle Request Exception when we should have access to a request and a response.
     *
     * This function is deprectaed and will be removed in a future release.
     *
     * @deprecated since v5.3.0
     *
     * @param RequestException $e
     *
     * @return self
     */
    protected static function fromGuzzleRequestException(RequestException $e): self
    {
        return static::fromGuzzleRequestOrConnectException($e);
    }

    /**
     * Factory to wrap a Guzzle Request or Connect Exception when we should have access
     * to a request and a optionally response
     *
     * @param RequestException|ConnectException $e
     *
     * @return self
     */
    private static function fromGuzzleRequestOrConnectException(GuzzleException $e): self
    {
        $response = $e instanceof RequestException ? $e->getResponse() : null;
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

    /**
     * Returns the response that caused the exception, if available.
     * Returns null if the exception does not have a response.
     *
     * Since Guzzle ^7.0 the ConnectException does not have a response, so null is returned.
     *
     * @return ResponseInterface|null
     */
    public function getResponse(): ?ResponseInterface
    {
        if (! $this->guzzleException instanceof RequestException) {
            return null;
        }

        return $this->guzzleException->getResponse();
    }

    /**
     * Returns the request that caused the exception, if available.
     * Returns null if the exception does not have a request.
     *
     * @return RequestInterface|null
     */
    public function getRequest(): ?RequestInterface
    {
        if (! $this->guzzleException instanceof RequestException &&
            ! $this->guzzleException instanceof ConnectException
        ) {
            return null;
        }

        return $this->guzzleException->getRequest();
    }
}
