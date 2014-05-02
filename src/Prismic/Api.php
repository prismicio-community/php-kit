<?php

/*
 * This file is part of the Prismic PHP SDK
 *
 * Copyright 2013 Zengularity (http://www.zengularity.com).
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Prismic;

use Guzzle\Http\Client;
use Guzzle\Plugin\Cache\CachePlugin;
use Guzzle\Plugin\Cache\DefaultCacheStorage;
use Prismic\FieldForm;

class Api
{
    protected $accessToken;
    protected $data;

    /**
     * @param string $data
     * @param string $accessToken
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
        $refs = $this->data->getRefs();
        $groupBy = array();
        foreach ($refs as $ref) {
            if (isset($groupBy[$ref->getLabel()])) {
                $arr = $groupBy[$ref->getLabel()];
                array_push($arr, $ref);
                $groupBy[$ref->getLabel()] = $arr;
            } else {
                $groupBy[$ref->getLabel()] = array($ref);
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
        return $this->data->getBookmarks();
    }

    public function bookmark($name)
    {
        if (isset($this->bookmarks()->{$name})) {
            return $this->bookmarks()->{$name};
        }

        return null;
    }

    /**
     * returns the master reference repository
     *
     * @return string
     */
    public function master()
    {
        $masters = array_filter($this->data->getRefs(), function ($ref) {
            return $ref->isMasterRef() == true;
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
        $forms = $this->data->getForms();
        $rforms = new \stdClass();
        foreach ($forms as $key => $form) {

            $fields = array();
            foreach ($form->fields as $name => $field) {
                $maybeDefault = isset($field->default) ? $field->default : null;
                $isMultiple = isset($field->multiple) ? $field->multiple : false;
                $fields[$name] = new FieldForm($field->type, $isMultiple, $maybeDefault);
            }

            $f = new Form(
                isset($form->name) ? $form->name : null,
                $form->method,
                isset($form->rel) ? $form->rel : null,
                $form->enctype,
                $form->action,
                $fields
            );

            $data = $f->defaultData();
            $rforms->$key = new SearchForm($this, $f, $data);
        }

        return $rforms;
    }

    /**
     * @return string
     */
    public function oauthInitiateEndpoint()
    {
        return $this->data->getOauthInitiate();
    }

    /**
     * @return string
     */
    public function oauthTokenEndpoint()
    {
        return $this->data->getOauthToken();
    }

    public function getData()
    {
        return $this->data;
    }

    /**
     * This method is static to respect others API
     *
     * @param string $action the URL endpoint of the repository's API
     * @param string $accessToken an optional permanent access token (leave null is you're not sure what you're doing)
     * @param string $client the client being used, or if null, will used a default client
     * @param bool|Guzzle\Cache\CacheAdapterInterface $cache the cache adapter being used, which must implement CacheAdapterInterface; set to true (default value) if you want the out-of-the box cache (a LRU cache of 100 entries); set to false if you want to disable the cache. If you want an LRU of 1000 entries, for instance, you can pass new LruCacheAdapter(1000).
     *
     * @return Api
     */
    public static function get($action, $accessToken = null, $client = null, $cache = true)
    {
        $url = $action . ($accessToken ? '?access_token=' . $accessToken : '');
        $client = isset($client) ? $client : self::defaultClient();

        // Adding the cache in case it's provided
        if (is_bool($cache)) {
            if ($cache) {
                $client->addSubscriber( new CachePlugin(array('storage' => new DefaultCacheStorage(new LruCacheAdapter(100)))) );
            }
        }
        else {
            $client->addSubscriber( new CachePlugin(array('storage' => new DefaultCacheStorage($cache))) );
        }

        $request = $client->get($url);
        $response = $request->send();

        $response = @json_decode($response->getBody(true));

        if (!$response) {
            throw new \RuntimeException('Unable to decode the json response');
        }

        $apiData = new ApiData(
            array_map(
                function ($ref) {
                    return Ref::parse($ref);
                },
                $response->refs
            ),
            $response->bookmarks,
            $response->types,
            $response->tags,
            $response->forms,
            $response->oauth_initiate,
            $response->oauth_token
        );

        return new Api($apiData, $accessToken);
    }

    public static function defaultClient()
    {
        return new Client('', array(
            Client::CURL_OPTIONS => array(
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 60,
                CURLOPT_USERAGENT      => 'prismic-php-kit',
                CURLOPT_HTTPHEADER     => array('Accept: application/json')
            )
        ));
    }
}
