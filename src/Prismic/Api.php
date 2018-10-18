<?php
declare(strict_types=1);

namespace Prismic;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Uri;
use Prismic\Document\Hydrator;
use Prismic\Document\HydratorInterface;
use Prismic\Exception;
use Psr\Cache\CacheException;
use Psr\Cache\CacheItemPoolInterface;
use stdClass;

/**
 * This class embodies a connection to your prismic.io repository's API.
 * Initialize it with Prismic::Api::get(), and use your Prismic::Api::forms() to make API calls
 * (read more in <a href="https://github.com/prismicio/php-kit">the kit's README file</a>)
 */
class Api
{

    /**
     * Kit version number
     * @deprecated
     */
    public const VERSION = "4.0.0";

    private const API_VERSION_1 = '1.0.0';

    private const API_VERSION_2 = '2.0.0';

    /**
     * Name of the cookie that will be used to remember the preview reference
     */
    public const PREVIEW_COOKIE = "io.prismic.preview";

    /**
     * Name of the cookie that will be used to remember the experiment reference
     */
    public const EXPERIMENTS_COOKIE = "io.prismic.experiment";

    /**
     * The API's access token to be used with each API call
     * @var string|null
     */
    private $accessToken;

    /**
     * An instance of ApiData containing information about types, tags and refs etc
     * @var ApiData
     */
    private $data;

    /**
     * The cache instance
     * @var CacheItemPoolInterface
     */
    private $cache;

    /**
     * Guzzle HTTP Client
     * @var ClientInterface
     */
    private $httpClient;

    /**
     * @var HydratorInterface
     */
    private $hydrator;

    /**
     * The API version determined by the URL passes to the named constructor
     * @var string
     */
    private $version;

    /**
     * @var LinkResolver|null
     */
    private $linkResolver;

    /** @var SearchFormCollection|null */
    private $forms;

    private function __construct()
    {
    }

    /**
     * This is the factory method with which to retrieve your API client instance
     *
     * If your API is set to "public" or "open", you can instantiate your Api object just like this:
     * Api::get('https://your-repository-name.prismic.io/api/v2')
     *
     * @param string $action The URL of your repository API's endpoint
     * @param string $accessToken A permanent access token to use if your repository API is set to private
     * @param ClientInterface $httpClient Custom Guzzle http client
     * @param CacheItemPoolInterface $cache Cache implementation
     * @return self
     */
    public static function get(
        string                  $action,
        ?string                 $accessToken = null,
        ?ClientInterface        $httpClient = null,
        ?CacheItemPoolInterface $cache = null
    ) : self {

        $api = new static();

        $api->accessToken = empty($accessToken) ? null : $accessToken;

        $api->httpClient = is_null($httpClient)
                         ? new Client()
                         : $httpClient;

        $api->cache = is_null($cache)
                    ? Cache\DefaultCache::factory()
                    : $cache;

        $api->version = \preg_match('~/v2$~i', $action) ? static::API_VERSION_2 : static::API_VERSION_1;

        $api->setHydrator(new Hydrator($api, [], Document::class));

        $url = new Uri($action);
        $url = $api->accessToken
            ? Uri::withQueryValue($url, 'access_token', $api->accessToken)
            : $url;

        $key = static::generateCacheKey((string) $url);
        try {
            $cacheItem  = $api->cache->getItem($key);
        } catch (CacheException $cacheException) {
            throw new Exception\RuntimeException(
                'A cache exception occurred whilst retrieving cached api data',
                0,
                $cacheException
            );
        }
        if ($cacheItem->isHit()) {
            $api->data = $cacheItem->get();
            return $api;
        }

        try {
            /** @var \Psr\Http\Message\ResponseInterface $response */
            $response = $api->httpClient->request('GET', (string) $url);
        } catch (GuzzleException $guzzleException) {
            throw Exception\RequestFailureException::fromGuzzleException($guzzleException);
        }

        $api->data = ApiData::withJsonString((string) $response->getBody());

        $cacheItem->set($api->data);
        $api->cache->save($cacheItem);

        return $api;
    }

    public function getHydrator() : HydratorInterface
    {
        return $this->hydrator;
    }

    public function setHydrator(HydratorInterface $hydrator)
    {
        $this->hydrator = $hydrator;
    }

    public function setLinkResolver(LinkResolver $linkResolver) : void
    {
        $this->linkResolver = $linkResolver;
    }

    public function getLinkResolver() :? LinkResolver
    {
        return $this->linkResolver;
    }

    public static function generateCacheKey(string $url) : string
    {
        return md5($url);
    }

    public function getApiVersion() : string
    {
        return $this->version;
    }

    public function isV1Api() : bool
    {
        return $this->version === static::API_VERSION_1;
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
     * Returns all forms of type Prismic::SearchForm that are available for this repository's API.
     * The intended syntax of a call is: api->forms()->everything->query(query)->ref(ref)->submit().
     * Learn more about those keywords in prismic.io's documentation on our developers' portal.
     */
    public function forms() : SearchFormCollection
    {
        if (! $this->forms) {
            $forms = [];
            foreach ($this->data->getForms() as $name => $jsonObject) {
                $formObject = Form::withJsonObject($jsonObject);
                $data = $formObject->defaultData();
                $forms[$name] = new SearchForm($this, $formObject, $data);
            }
            $this->forms = new SearchFormCollection($forms);
        }

        return $this->forms;
    }

    public function getExperiments() : Experiments
    {
        return $this->data->getExperiments();
    }

    /**
     * Return the URL to display a given preview
     * @param string $token as received from Prismic server to identify the content to preview
     * @param string $defaultUrl the URL to return if the preview doesn't correspond to a document
     * @return string the URL you should redirect the user to preview the requested change
     * @throws Exception\ExceptionInterface If there's a problem querying the API
     */
    public function previewSession(string $token, string $defaultUrl) : string
    {
        try {
            $response = $this->httpClient->request('GET', $token);
        } catch (GuzzleException $guzzleException) {
            throw Exception\RequestFailureException::fromGuzzleException($guzzleException);
        }
        /** @var \Psr\Http\Message\ResponseInterface $response */
        $response = \json_decode((string) $response->getBody());
        if (isset($response->mainDocument)) {
            $document = $this->getById(
                $response->mainDocument,
                ['ref' => $token]
            );
            if ($document && $this->linkResolver) {
                $url = $this->linkResolver->resolve($document->asLink());
                return $url ? $url : $defaultUrl;
            }
        }
        return $defaultUrl;
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
    public function getCache() : CacheItemPoolInterface
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
     * If a preview cookie is set, return the ref stored in that cookie
     */
    private function getPreviewRef() :? string
    {
        $cookieNames = [
            str_replace(['.',' '], '_', static::PREVIEW_COOKIE),
            static::PREVIEW_COOKIE,
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
            str_replace(['.',' '], '_', static::EXPERIMENTS_COOKIE),
            static::EXPERIMENTS_COOKIE,
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
        return $this->master()->getRef();
    }

    /**
     * Shortcut to query on the default reference.
     * Use the reference from previews or experiment cookie, fallback to the master reference otherwise.
     *
     * @param  string|array|Predicate $q         the query, as a string, predicate or array of predicates
     * @param  array                  $options   query options: pageSize, orderings, etc.
     * @return Response
     * @throws Exception\ExceptionInterface if parameters are invalid
     */
    public function query($q, array $options = []) : Response
    {
        $options = $this->prepareDefaultQueryOptions($options);
        $ref = $this->ref();

        $form = $this->forms()->getForm('everything');
        if (! $form) {
            throw new Exception\RuntimeException('The form "everything" does not exist');
        }
        $form = $form->ref($ref);
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
     * @param  string|array|Predicate $q The query, as a string, predicate or array of predicates
     * @param  array $options Query options: pageSize, orderings, etc.
     * @return DocumentInterface|null The resulting document, or null
     * @throws Exception\ExceptionInterface if parameters are invalid
     */
    public function queryFirst($q, array $options = []) :? DocumentInterface
    {
        $documents = $this->query($q, $options)->getResults();
        if (count($documents) > 0) {
            return $documents[0];
        }
        return null;
    }

    /**
     * Search a document by its id
     *
     * @param string $id The requested id
     * @param array $options Query options: pageSize, orderings, etc.
     * @return DocumentInterface|null The resulting document (null if no match)
     * @throws Exception\ExceptionInterface if parameters are invalid
     */
    public function getById(string $id, array $options = []) :? DocumentInterface
    {
        return $this->queryFirst(Predicates::at("document.id", $id), $options);
    }

    /**
     * Search a document by its uid
     *
     * @param string $type The custom type of the requested document
     * @param string $uid The requested uid
     * @param array $options Query options: pageSize, orderings, etc.
     * @return DocumentInterface|null The resulting document (null if no match)
     * @throws Exception\ExceptionInterface if parameters are invalid
     */
    public function getByUid(string $type, string $uid, array $options = []) :? DocumentInterface
    {
        return $this->queryFirst(Predicates::at("my.".$type.".uid", $uid), $options);
    }

    /**
     * Return a set of document from their ids
     *
     * @param array $ids array of strings, the requested ids
     * @param array $options query options: pageSize, orderings, etc.
     * @return Response the response, including documents and pagination information
     * @throws Exception\ExceptionInterface if parameters are invalid
     */
    public function getByIds(array $ids, array $options = []) : Response
    {
        return $this->query(Predicates::in("document.id", $ids), $options);
    }

    /**
     * Get a single typed document by its type
     *
     * @param string $type The custom type of the requested document
     * @param array $options Query options: pageSize, orderings, etc.
     * @return DocumentInterface|null The resulting document (null if no match)
     * @throws Exception\ExceptionInterface if parameters are invalid
     */
    public function getSingle(string $type, array $options = []) :? DocumentInterface
    {
        return $this->queryFirst(Predicates::at("document.type", $type), $options);
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
