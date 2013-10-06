<?php

namespace Prismic;

use Guzzle\Http\ClientInterface;
use Guzzle\Http\Client;

class Api
{
    protected $accessToken;
    protected $data;

    protected static $client;

    /**
     * @param string $data
     * @param null   $accessToken
     */
    private function __construct($data, $accessToken = null)
    {
        $this->data        = $data;
        $this->accessToken = $accessToken;
    }

    /**
     * returns all repositories references
     *
     * @return array
     */
    public function refs()
    {
        $refs = $this->data->refs;
        $groupBy = array();
        foreach ($refs as $ref) {
            if (isset($refs[$ref->label])) {
                $arr = $refs[$ref->label];
                array_push($arr, $ref);
                $groupBy[$ref->label] = $arr;
            } else {
                $groupBy[$ref->label] = array($ref);
            }
        }

        $results = array();
        foreach ($groupBy as $label => $values) {
            $results[$label] = $values[0];
        }

        return $results;
    }

    public function bookmarks()
    {
        return $this->data->bookmarks;
    }

    /**
     * returns the master reference repository
     *
     * @return string
     */
    public function master()
    {
        $masters = array_filter($this->data->refs, function ($ref) {
            return $ref->isMasterRef == true;
        });

        return $masters[0];
    }

    /**
     * returns all forms availables
     *
     * @return mixed
     */
    public function forms()
    {
        $forms = $this->data->forms;
        foreach ($forms as $key => $form) {
            $f = new Form(
                isset($form->name) ? $form->name : null,
                $form->method,
                isset($form->rel) ? $form->rel : null,
                $form->enctype,
                $form->action,
                $form->fields
            );

            $data = $f->defaultData();

            if ($this->accessToken) {
                $data['access_token'] = $this->accessToken;
            }

            $forms->$key = new SearchForm($this, $f, $data);
        }

        return $forms;
    }

    /**
     * @return string
     */
    public function oauthInitiateEndpoint()
    {
        return $this->data->oauth_initiate;
    }

    /**
     * @return string
     */
    public function oauthTokenEndpoint()
    {
        return $this->data->oauth_token;
    }

    /**
     * This method is static to respect the others API
     *
     * @param string $action
     * @param string $accessToken
     *
     * @return Api
     */
    public static function get($action, $accessToken = null)
    {
        $url = $action . ($accessToken ? '?access_token=' . $accessToken : '');

        $request = self::getClient()->get($url);

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

        return new Api($apiData, $accessToken);
    }

    /**
     * This is an entry point to alter the client used by the API
     *
     * @param \Guzzle\Http\ClientInterface $client
     */
    public static function setClient(ClientInterface $client)
    {
        self::$client = $client;
    }

    /**
     * @return \Guzzle\Http\Client
     */
    public static function getClient()
    {
        if (self::$client === null) {
            self::$client = new Client('', array(
                Client::CURL_OPTIONS => array(
                    CURLOPT_CONNECTTIMEOUT => 10,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT        => 60,
                    CURLOPT_USERAGENT      => 'prismic-php-0.1',
                    CURLOPT_HTTPHEADER     => array('Accept: application/json')
                )
            ));
        }

        return self::$client;
    }
}