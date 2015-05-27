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

use Ivory\HttpAdapter\ConfigurationInterface;
use Ivory\HttpAdapter\Configuration;
use Ivory\HttpAdapter\CurlHttpAdapter;
use Ivory\HttpAdapter\Event\Subscriber\StatusCodeSubscriber;
use Ivory\HttpAdapter\HttpAdapterInterface;
use \Prismic\Cache\CacheInterface;
use \Prismic\Cache\ApcCache;
use \Prismic\Cache\NoCache;

/**
 * @deprecated deprecated since version 1.5.3, use Api::PREVIEW_COOKIE;
 */
const PREVIEW_COOKIE = Api::PREVIEW_COOKIE;

/**
 * @deprecated deprecated since version 1.5.3, use Api::EXPERIMENTS_COOKIE;
 */
const EXPERIMENTS_COOKIE = Api::EXPERIMENTS_COOKIE;

/**
 * This class embodies a connection to your prismic.io repository's API.
 * Initialize it with Prismic\Api::get(), and use your Prismic\Api::forms() to make API calls
 * (read more in <a href="https://github.com/prismicio/php-kit">the kit's README file</a>)
 *
 * @api
 */
class Api
{

    const VERSION = "1.5.9";

    const PREVIEW_COOKIE = "io.prismic.preview";

    const EXPERIMENTS_COOKIE = "io.prismic.experiment";

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
     * @var HttpAdapterInterface
     */
    private $httpAdapter;

    /**
     * Private constructor, not be used outside of this class.
     *
     * @param string                    $data
     * @param string                    $accessToken
     * @param HttpAdapterInterface|null $httpAdapter
     * @param CacheInterface|null       $cache
     */
    private function __construct($data, $accessToken = null, HttpAdapterInterface $httpAdapter = null, CacheInterface $cache = null)
    {
        $this->data        = $data;
        $this->accessToken = $accessToken;
        $this->httpAdapter = is_null($httpAdapter) ? self::defaultHttpAdapter() : $httpAdapter;
        $this->cache = is_null($cache) ? self::defaultCache() : $cache;
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
     * @param $label string the label of the requested ref
     * @return Ref a reference or null
     */
    public function getRef($label) {
        $refs = $this->refs();
        return $refs[$label];
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
        $bookmarks = $this->bookmarks();
        if (isset($bookmarks[$name])) {
            return $bookmarks[$name];
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
     * @return \stdClass all forms
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

    public function getExperiments()
    {
        return $this->data->getExperiments();
    }

    /**
     * Return the URL to display a given preview
     * @param string $token as received from Prismic server to identify the content to preview
     * @param \Prismic\LinkResolver $linkResolver the link resolver to build URL for your site
     * @param string $defaultUrl the URL to default to return if the preview doesn't correspond to a document
     *                (usually the home page of your site)
     * @return string the URL you should redirect the user to preview the requested change
     */
    public function previewSession($token, $linkResolver, $defaultUrl)
    {
        $response = $this->getHttpAdapter()->get($token);
        $response = json_decode($response->getBody(true));
        if (isset($response->mainDocument)) {
            $documents = $this->forms()->everything
                ->query(Predicates::at("document.id", $response->mainDocument))
                ->ref($token)
                ->submit()
                ->getResults();
            if (count($documents) > 0) {
                return $linkResolver->resolveDocument($documents[0]);
            }
        }
        return $defaultUrl;
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
     * Accessing the underlaying HTTP adapter object responsible for the CURL requests
     *
     * @return HttpAdapterInterface the HTTP adapter object itself
     */
    public function getHttpAdapter()
    {
        return $this->httpAdapter;
    }

    /**
     * This is the endpoint to build your API, and is a static method.
     * If your API is set to "public" or "open", you can instantiate your Api object just like this:
     * Api::get('http://idofyourrepository.prismic.io/api')
     *
     * @api
     *
     * @param  string $action the URL of your repository API's endpoint
     * @param  string $accessToken a permanent access token to use to access your content, for instance if your repository API is set to private
     * @param  HttpAdapterInterface $httpAdapter by default, the HTTP adapter uses CURL with a certain configuration, but you can override it here
     * @param  CacheInterface $cache Cache implementation
     * @throws \RuntimeException
     * @return Api                     the Api object, useable to perform queries
     */
    public static function get($action, $accessToken = null, HttpAdapterInterface $httpAdapter = null, CacheInterface $cache = null)
    {
        $cache = is_null($cache) ? self::defaultCache() : $cache;
        $cacheKey = $action . (is_null($accessToken) ? "" : ("#" . $accessToken));
        $apiData = $cache->get($cacheKey);
        $api = $apiData ? new Api(unserialize($apiData), $accessToken, $httpAdapter, $cache) : null;
        if ($api) {
            return $api;
        } else {
            $url = $action . ($accessToken ? '?access_token=' . $accessToken : '');
            $httpAdapter = is_null($httpAdapter) ? self::defaultHttpAdapter() : $httpAdapter;
            $response = $httpAdapter->get($url);
            $response = json_decode($response->getBody(true));
            $experiments = isset($response->experiments)
                         ? Experiments::parse($response->experiments)
                         : new Experiments(array(), array());

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
                (array)$response->bookmarks,
                (array)$response->types,
                $response->tags,
                (array)$response->forms,
                $experiments,
                $response->oauth_initiate,
                $response->oauth_token
            );

            $api = new Api($apiData, $accessToken, $httpAdapter, $cache);
            $cache->set($cacheKey, serialize($apiData), 5);

            return $api;
        }
    }

    /**
     * Submit several requests in parrallel
     */
    public function submit()
    {
        $numargs = func_num_args();
        if ($numargs == 1 && is_array(func_get_arg(0))) {
            $forms = func_get_arg(0);
        } else {
            $forms = func_get_args();
        }
        $responses = array();

        // Get what we can from the cache
        $all_urls = array();
        $urls = array();
        foreach ($forms as $i => $form) {
            $url = $form->url();
            array_push($all_urls, $url);
            $json = $this->getCache()->get($url);
            if ($json) {
                $responses[$i] = Response::parse($json);
            } else {
                $responses[$i] = null;
                array_push($urls, $url);
            }
        }

        // Query the server for the rest
        if (count($urls) > 0) {
            $raw_responses = $this->getHttpAdapter()->sendRequests($urls);
            foreach ($raw_responses as $response) {
                $url = $response->getParameter('request')->getUrl();
                $cacheControl = $response->getHeader('Cache-Control');
                $cacheDuration = null;
                if (preg_match('/^max-age\s*=\s*(\d+)$/', $cacheControl, $groups) == 1) {
                    $cacheDuration = (int) $groups[1];
                }
                $json = json_decode($response->getBody(true));
                if (!isset($json)) {
                    throw new \RuntimeException("Unable to decode json response");
                }
                if ($cacheDuration !== null) {
                    $expiration = $cacheDuration;
                    $this->getCache()->set($url, $json, $expiration);
                }

                $idx = array_search($url, $all_urls);
                $responses[$idx] = Response::parse($json);
            }
        }

        return $responses;
    }

    /**
     * Use the APC cache if APC is activated on the server, otherwise fallback to the noop cache (no cache)
     */
    public static function defaultCache()
    {
        if (extension_loaded('apc') && ini_get('apc.enabled')) {
            return new ApcCache();
        }
        return new NoCache();
    }

    /**
     * Get the default HTTP adapter configuration object
     *
     * This can be used for example to modify but not completely replace the
     * default configuration (e.g. to prefix the user agent string), or to use
     * the default configuration for a non-default HTTP adapter.
     *
     * @return \Ivory\HttpAdapter\ConfigurationInterface Configuration object
     */
    public static function defaultHttpAdapterConfiguration()
    {
        $configuration = new Configuration();
        $configuration->setUserAgent('Prismic-php-kit/' . self::VERSION . ' PHP/' . phpversion());

        // We need to add the subscriber to have errors on 4.x.x and 5.x.x.
        $statusCodeSubscriber = new StatusCodeSubscriber();
        $configuration->getEventDispatcher()->addSubscriber($statusCodeSubscriber);

        return $configuration;
    }

    /**
     * Get the default HTTP adapter used in the kit; this is entirely
     * overridable by passing an instance of
     * Ivory\HttpAdapter\HttpAdapterInterface to Api.get
     *
     * @param ConfigurationInterface|null $configuruation Configuration object;
     * use default if null
     * @return HttpAdapterInterface HTTP adapter
     */
    public static function defaultHttpAdapter(ConfigurationInterface $configuration = null)
    {
        if ($configuration === null) {
            $configuration = self::defaultHttpAdapterConfiguration();
        }
        return new CurlHttpAdapter($configuration);
    }
}
