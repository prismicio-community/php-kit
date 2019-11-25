<?php
declare(strict_types=1);

namespace Prismic;

use Prismic\Exception;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Promise;
use GuzzleHttp\Exception\GuzzleException;
use Prismic\Cache\CacheInterface;
use Prismic\Cache\ApcCache;
use Prismic\Cache\NoCache;
use stdClass;

/**
 * This class embodies a connection to your Prismic repository's API.
 * Initialize it with Prismic::Api::get(), and use your Prismic::Api::form() to make API calls
 * (read more in <a href="https://github.com/prismicio/php-kit">the kit's README file</a>)
 */
class Api
{

    /**
     * Kit version number
     */
    const VERSION = "5.0.0";

    /**
     * Name of the cookie that will be used to remember the preview reference
     */
    const PREVIEW_COOKIE = "io.prismic.preview";

    /**
     * Name of the cookie that will be used to remember the experiment reference
     */
    const EXPERIMENTS_COOKIE = "io.prismic.experiment";

    /**
     * The API's access token to be used with each API call
     * @var string|null
     */
    protected $accessToken;

    /**
     * An instance of ApiData containing information about types, tags and refs etc
     * @var ApiData
     */
    protected $data;

    /**
     * The cache instance
     * @var CacheInterface
     */
    private $cache;

    /**
     * Guzzle HTTP Client
     * @var ClientInterface
     */
    private $httpClient;

    private function __construct(
        ApiData $data,
        ?string $accessToken = null,
        ?ClientInterface $httpClient = null,
        ?CacheInterface $cache = null
    ) {
        $this->data        = $data;
        $this->accessToken = $accessToken;
        $this->httpClient  = is_null($httpClient) ? new Client() : $httpClient;
        $this->cache       = is_null($cache) ? static::defaultCache() : $cache;
    }

    /**
     * This is the factory method with which to retrieve your API client instance
     *
     * If your API is set to "public" or "open", you can instantiate your Api object just like this:
     * Api::get('https://your-repository-name.prismic.io/api/v2')
     *
     * @param  string          $action      The URL of your repository API's endpoint
     * @param  string          $accessToken A permanent access token to use if your repository API is set to private
     * @param  ClientInterface $httpClient  Custom Guzzle http client
     * @param  CacheInterface  $cache       Cache implementation
     * @param  int             $apiCacheTTL Max time to keep the API object in cache (in seconds)
     * @return static
     */
    public static function get(
        string            $action,
        ?string           $accessToken = null,
        ?ClientInterface  $httpClient = null,
        ?CacheInterface   $cache = null,
        int               $apiCacheTTL = 5
    ) : self {
        $cache    = is_null($cache) ? static::defaultCache() : $cache;
        $cacheKey = $action . (empty($accessToken) ? "" : ("#" . $accessToken));
        $apiData  = $cache->get($cacheKey);

        if (is_string($apiData) && ! empty($apiData)) {
            return new static(unserialize($apiData), $accessToken, $httpClient, $cache);
        }

        $url = $accessToken ? Utils::buildUrl($action, [ 'access_token' => $accessToken]) : $action;
        $httpClient = is_null($httpClient) ? new Client() : $httpClient;
        try {
            /** @var \Psr\Http\Message\ResponseInterface $response */
            $response = $httpClient->request('GET', $url);
        } catch (GuzzleException $guzzleException) {
            throw Exception\RequestFailureException::fromGuzzleException($guzzleException);
        }

        $apiData = ApiData::withJsonString((string) $response->getBody());
        $api = new static($apiData, $accessToken, $httpClient, $cache);
        $cache->set($cacheKey, serialize($apiData), $apiCacheTTL);

        return $api;
    }

    /**
     * Returns all of the repository's references (queryable points in time)
     *
     * @return Ref[]
     */
    public function refs() : array
    {
        $groupBy = [];
        foreach ($this->data->getRefs() as $ref) {
            $label = $ref->getLabel();
            if (! isset($groupBy[$label])) {
                $groupBy[$label] = $ref;
            }
        }

        return $groupBy;
    }

    /**
     * Return the ref identified by the given label
     *
     * @param string $label The label of the requested ref
     *
     * @return Ref|null a reference or null
     */
    public function getRefFromLabel(string $label) :? Ref
    {
        $refs = $this->refs();
        return $refs[$label];
    }

    /**
     * Returns the list of all bookmarks on the repository. If you're looking
     * for a document from it's bookmark name, you should use the bookmark() function.
     *
     * @return array the array of bookmarks
     */
    public function bookmarks() : array
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
     * @param string $name the bookmark name to use
     *
     * @return string|null the ID string for a given bookmark name
     */
    public function bookmark(string $name) :? string
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
     * @return Ref the master ref
     */
    public function master() : Ref
    {
        $masters = array_filter($this->data->getRefs(), function (Ref $ref) {
            return $ref->isMasterRef() == true;
        });

        return $masters[0];
    }

    /**
     * Returns the form of type Prismic::SearchForm based on its name.
     * The intended syntax of a call is: api->form('everything')->query(query)->ref(ref)->submit().
     * Learn more about those keywords in Prismic's documentation on our developers' portal.
     */
    public function form(string $formName) : SearchForm
    {
        $forms = $this->data->getForms();
        $formObject = Form::withJsonObject($forms[$formName]);
        $data = $formObject->defaultData();

        return new SearchForm($this->httpClient, $this->cache, $formObject, $data);
    }

    public function getExperiments() : Experiments
    {
        return $this->data->getExperiments();
    }

    /**
     * Return the URL to display a given preview
     * @param string $token as received from Prismic server to identify the content to preview
     * @param LinkResolver $linkResolver the link resolver to build URL for your site
     * @param string $defaultUrl the URL to default to return if the preview doesn't correspond to a document
     *                (usually the home page of your site)
     * @return string the URL you should redirect the user to preview the requested change
     */
    public function previewSession(string $token, LinkResolver $linkResolver, string $defaultUrl) : string
    {
        try {
            $response = $this->getHttpClient()->request('GET', $token);
        } catch (GuzzleException $guzzleException) {
            throw Exception\RequestFailureException::fromGuzzleException($guzzleException);
        }
        /** @var \Psr\Http\Message\ResponseInterface $response */
        $response = \json_decode((string) $response->getBody());
        if (isset($response->mainDocument)) {
            $documents = $this
                       ->query(Predicates::at("document.id", $response->mainDocument), ['ref' => $token, 'lang' => '*'])
                       ->results;
            if (count($documents) > 0) {
                if ($url = $linkResolver($documents[0])) {
                    return $url;
                }
            }
        }
        return $defaultUrl;
    }

    /**
     * Return the URL of the endpoint to initiate OAuth authentication.
     */
    public function oauthInitiateEndpoint() : string
    {
        return $this->data->getOauthInitiate();
    }

    /**
     * Return the URL of the endpoint to use OAuth authentication.
     */
    public function oauthTokenEndpoint() : string
    {
        return $this->data->getOauthToken();
    }

    /**
     * Accessing raw data returned by the /api endpoint
     */
    public function getData() : ApiData
    {
        return $this->data;
    }

    /**
     * Accessing the cache object specifying how to store the cache
     */
    public function getCache() : CacheInterface
    {
        return $this->cache;
    }

    /**
     * Accessing the underlying Guzzle HTTP client
     */
    public function getHttpClient() : ClientInterface
    {
        return $this->httpClient;
    }

    /**
     * Submit several requests in parallel
     *
     * @TODO Discover the use-case for this method and either refactor it or remove it
     * @return array
     */
    public function submit() : array
    {
        $numargs = func_num_args();
        if ($numargs == 1 && is_array(func_get_arg(0))) {
            $forms = func_get_arg(0);
        } else {
            $forms = func_get_args();
        }
        $responses = [];

        // Get what we can from the cache
        $all_urls = [];
        $promises = [];
        $urls = [];
        foreach ($forms as $i => $form) {
            $url = $form->url();
            array_push($all_urls, $url);
            $json = $this->getCache()->get($url);
            if ($json) {
                $responses[$i] = $json;
            } else {
                $responses[$i] = null;
                array_push($urls, $url);
                $promises[$url] = $this->getHttpClient()->getAsync($url);
            }
        }

        // Query the server for the rest
        if (count($promises) > 0) {
            $raw_responses = Promise\unwrap($promises);

            foreach ($urls as $url) {
                $response = $raw_responses[$url];
                $cacheControl = $response->getHeader('Cache-Control')[0];
                $cacheDuration = null;
                if (preg_match('/^max-age\s*=\s*(\d+)$/', $cacheControl, $groups) == 1) {
                    $cacheDuration = (int) $groups[1];
                }
                $json = json_decode($response->getBody(true));
                if (! isset($json)) {
                    throw new Exception\RuntimeException("Unable to decode json response");
                }
                if ($cacheDuration !== null) {
                    $expiration = $cacheDuration;
                    $this->getCache()->set($url, $json, $expiration);
                }

                $idx = array_search($url, $all_urls);
                $responses[$idx] = $json;
            }
        }

        return $responses;
    }

    /**
     * If a preview cookie is set, return the ref stored in that cookie
     */
    private function getPreviewRef() :? string
    {
        $cookieNames = [
            str_replace(['.',' '], '_', self::PREVIEW_COOKIE),
            self::PREVIEW_COOKIE,
        ];
        foreach ($cookieNames as $cookieName) {
            if (isset($_COOKIE[$cookieName])) {
                return $_COOKIE[$cookieName];
            }
        }

        return null;
    }

    /**
     * If an experiment cookie is set, return the ref as determined by \Prismic\Experiments::refFromCookie
     */
    private function getExperimentRef() :? string
    {
        $cookieNames = [
            str_replace(['.',' '], '_', self::EXPERIMENTS_COOKIE),
            self::EXPERIMENTS_COOKIE,
        ];
        foreach ($cookieNames as $cookieName) {
            if (isset($_COOKIE[$cookieName])) {
                $experiments = $this->getExperiments();
                return $experiments->refFromCookie($_COOKIE[$cookieName]);
            }
        }

        return null;
    }

    /**
     * Whether the current ref in use is a preview, i.e. the user is in preview mode
     */
    public function inPreview() : bool
    {
        return null !== $this->getPreviewRef();
    }

    /**
     * Whether the current ref in use is an experiment
     */
    public function inExperiment() : bool
    {
        return null !== $this->getExperimentRef() && false === $this->inPreview();
    }

    /**
     * Return the ref currently in use
     *
     * In order of preference, returns the preview cookie, the experiments cookie or the master ref otherwise
     */
    public function ref() : string
    {
        $preview = $this->getPreviewRef();
        if ($preview) {
            return $preview;
        }
        $experiment = $this->getExperimentRef();
        if ($experiment) {
            return $experiment;
        }
        return (string) $this->master()->getRef();
    }

    /**
     * Shortcut to query on the default reference.
     * Use the reference from previews or experiment cookie, fallback to the master reference otherwise.
     *
     * @param  string|array|Predicate $q         the query, as a string, predicate or array of predicates
     * @param  array                  $options   query options: pageSize, orderings, etc.
     * @return stdClass
     */
    public function query($q, array $options = []) : stdClass
    {
        $ref = $this->ref();
        /** @var SearchForm $form */
        $form = $this->form('everything')->ref($ref);
        if (! empty($q)) {
            $form = $form->query($q);
        }
        foreach ($options as $key => $value) {
            $form = $form->set($key, $value);
        }
        return $form->submit();
    }

    /**
     * Return the first document matching the query
     * Use the reference from previews or experiment cookie, fallback to the master reference otherwise.
     *
     * @param  string|array|Predicate $q        the query, as a string, predicate or array of predicates
     * @param  array                  $options  query options: pageSize, orderings, etc.
     *
     * @return stdClass|null     the resulting document, or null
     */
    public function queryFirst($q, array $options = []) :? stdClass
    {
        $documents = $this->query($q, $options)->results;
        if (count($documents) > 0) {
            return $documents[0];
        }
        return null;
    }

    /**
     * Search a document by its id
     *
     * @param string   $id          the requested id
     * @param array    $options     query options: pageSize, orderings, etc.
     *
     * @return stdClass|null the resulting document (null if no match)
     */
    public function getByID(string $id, array $options = []) :? stdClass
    {
        $options = $this->prepareDefaultQueryOptions($options);
        return $this->queryFirst(Predicates::at("document.id", $id), $options);
    }

    /**
     * Search a document by its uid
     *
     * @param string   $type          the custom type of the requested document
     * @param string   $uid           the requested uid
     * @param array    $options       query options: pageSize, orderings, etc.
     * @return stdClass|null the resulting document (null if no match)
     */
    public function getByUID(string $type, string $uid, array $options = []) :? stdClass
    {
        $options = $this->prepareDefaultQueryOptions($options);
        return $this->queryFirst(Predicates::at("my.".$type.".uid", $uid), $options);
    }

    /**
     * Return a set of document from their ids
     *
     * @param array   $ids     array of strings, the requested ids
     * @param array   $options query options: pageSize, orderings, etc.
     *
     * @return stdClass the response, including documents and pagination information
     */
    public function getByIDs(array $ids, array $options = []) : stdClass
    {
        $options = $this->prepareDefaultQueryOptions($options);
        return $this->query(Predicates::in("document.id", $ids), $options);
    }

    /**
     * Get a single typed document by its type
     *
     * @param string   $type        the custom type of the requested document
     * @param array    $options     query options: pageSize, orderings, etc.
     *
     * @return stdClass|null    the resulting document (null if no match)
     */
    public function getSingle(string $type, array $options = []) :? stdClass
    {
        return $this->queryFirst(Predicates::at("document.type", $type), $options);
    }

    /**
     * Use the APC cache if APC is activated on the server, otherwise fallback to the noop cache (no cache)
     */
    public static function defaultCache() : CacheInterface
    {
        if (extension_loaded('apc') && ini_get('apc.enabled')) {
            return new ApcCache();
        }
        return new NoCache();
    }

    /**
     * Given an options array for a query, fill the lang parameter with a default value
     * @param array $options
     * @return array
     */
    private function prepareDefaultQueryOptions(array $options) : array
    {
        if (! isset($options['lang'])) {
            $options['lang'] = '*';
        }

        return $options;
    }
}
