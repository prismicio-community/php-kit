<?php

namespace Prismic;

use Guzzle\Http\ClientInterface;
use Guzzle\Http\Client;

class Api
{
    protected $accessToken;

    /**
     * @param string $accessToken
     * @param ClientInterface $client
     */
    public function __construct($accessToken, ClientInterface $client = null)
    {
        $this->accessToken = $accessToken;
        $this->client = $client ?: new Client('', array(
            Client::CURL_OPTIONS => array(
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 60,
                CURLOPT_USERAGENT      => 'prismic-php-0.1',
                CURLOPT_HTTPHEADER     => array('Accept: application/json')
            )
        ));

        $this->client->setDefaultOption('allow_redirects', false);
        $this->client->setDefaultOption('exceptions', true);
    }

    /**
     * @param string $action
     * @param string $maybeAccessToken
     *
     * @return Api
     */
    public function get($action, $maybeAccessToken = null)
    {
        $paramToken = isset($maybeAccessToken) ? '?access_token=' . $maybeAccessToken : '';

        $request = $this->client->get($action . $paramToken);

        $response = $request->send();

        $response = @json_decode($response->getBody(true));

        if (!$response) {
            throw new \RuntimeException('Unable to decode the json response');
        }

        $apiData = new ApiData(
            array_map(function ($ref) {
               return Ref::parse($ref);
            }, $response->refs),
            $response->bookmarks,
            $response->types,
            $response->tags,
            $response->forms,
            $response->oauth_initiate,
            $response->oauth_token
        );

        return new Response($this->client, $apiData, $maybeAccessToken);
    }
}