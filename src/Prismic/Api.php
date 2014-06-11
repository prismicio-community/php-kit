<?php
/**
 * This file is part of the Prismic PHP SDK
 *
 * Copyright 2013 Zengularity (http://www.zengularity.com).
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Prismic;

use Guzzle\Http\Client;
use \Prismic\Cache\CacheInterface;
use \Prismic\Cache\DefaultCache;

/**
 * This class embodies a connection to your prismic.io repository's API.
 * Initialize it with Prismic\Api::get(), and use your Prismic\Api::forms() to make API calls
 * (read more in <a href="https://github.com/prismicio/php-kit">the kit's README file</a>)
 *
 * @api
 */
class Api
{
    /**
     * @var string the API's access token to be used with each API call
     */
    protected $accessToken;
    /**
     * @var ApiData the raw data of the /api document (prefer to use this class's instance methods)
     */
    protected $data;
    /**
     * @var CacheInterface the cache object specifying how to store the cache
     */
    private $cache;

    /**
     * Private constructor, not be used outside of this class.
     *
     * @param string $data
     * @param string $accessToken
     */
    private function __construct($data, $accessToken = null, CacheInterface $cache = null)
    {
        $this->data        = $data;
        $this->accessToken = $accessToken;
        $this->cache = is_null($cache) ? new DefaultCache() : $cache;
    }

    /**
     * Returns all of the repository's references (queryable points in time)
     *
     * @api
     *
     * @return array the array of references, with their IDs, labels, ...
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

    /**
     * Returns the list of all bookmarks on the repository. If you're looking
     * for a document from it's bookmark name, you should use the bookrmark() function.
     *
     * @api
     *
     * @return array the array of bookmarks
     */
    public function bookmarks()
    {
        return $this->data->getBookmarks();
    }

    /**
     * From a bookmark name, returns the ID of the attached document.
     * You can then use this ID for anything, for instance to query with a predicate
     * that looks like this [:d = at(document.id, "abcdefghijkl")].
     * Most starter projects embed a helper to query a document from their ID string,
     * which makes this even easier.
     *
     * @api
     *
     * @param string $name the bookmark name to use
     *
     * @return string the ID string for a given bookmark name
     */
    public function bookmark($name)
    {
        if (isset($this->bookmarks()->{$name})) {
            return $this->bookmarks()->{$name};
        }

        return null;
    }

    /**
     * Returns the master ref repository: the ref which is to be used to query content
     * that is live right now.
     *
     * @api
     *
     * @return string the master ref
     */
    public function master()
    {
        $masters = array_filter($this->data->getRefs(), function ($ref) {
            return $ref->isMasterRef() == true;
        });

        return $masters[0];
    }

    /**
     * Returns all forms of type Prismic\SearchForm that are available for this repository's API.
     * The intended syntax of a call is: api->forms()->everything->query(query)->ref(ref)->submit().
     * Learn more about those keywords in prismic.io's documentation on our developers' portal.
     *
     * @api
     *
     * @return array all forms
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
     * Returning the URL of the endpoint to initiate OAuth authentication.
     *
     * @return string the URL of the endpoint
     */
    public function oauthInitiateEndpoint()
    {
        return $this->data->getOauthInitiate();
    }

    /**
     * Returning the URL of the endpoint to use OAuth authentication.
     *
     * @return string the URL of the endpoint
     */
    public function oauthTokenEndpoint()
    {
        return $this->data->getOauthToken();
    }

    /**
     * Accessing raw data returned by the /api endpoint
     *
     * @return ApiData the raw data
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Accessing the cache object specifying how to store the cache
     *
     * @return CacheInterface the cache object itself
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * This is the endpoint to build your API, and is a static method.
     * If your API is set to "public" or "open", you can instantiate your Api object just like this:
     * Api::get('http://idofyourrepository.prismic.io/api')
     *
     * @api
     *
     * @param  string              $action      the URL of your repository API's endpoint
     * @param  string              $accessToken a permanent access token to use to access your content, for instance if your repository API is set to private
     * @param  \Guzzle\Http\Client $client      by default, the client is a Guzzle with a certain configuration, but you can override it here
     * @param  CacheInterface      $cache       Cache implementation
     * @return Api                 the Api object, useable to perform queries
     */
    public static function get($action, $accessToken = null, $client = null, $cache = null)
    {
        $cache = is_null($cache) ? new DefaultCache() : $cache;
        $cacheKey = $action . (is_null($accessToken) ? "" : ("#" . $accessToken));

        $api = $cache->get($cacheKey);
        $api = $api ? unserialize($api) : null;

        if ($api) {
            return $api;
        } else {
            $url = $action . ($accessToken ? '?access_token=' . $accessToken : '');
            $client = isset($client) ? $client : self::defaultClient();
            $request = $client->get($url);
            $response = $request->send();
            $response = json_decode($response->getBody(true));

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

            $api = new Api($apiData, $accessToken, $cache);
            $cache->set($cacheKey, serialize($api), 5);

            return $api;
        }
    }

    /**
     * The default configuration of the client used in the kit; this is entirely overridable by passing
     */
    public static function defaultClient()
    {
        return new Client('', array(
            Client::CURL_OPTIONS => array(
                CURLOPT_CONNECTTIMEOUT => 10,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 60,
                CURLOPT_USERAGENT      => 'prismic-php-0.1',
                CURLOPT_HTTPHEADER     => array('Accept: application/json')
            )
        ));
    }
}
