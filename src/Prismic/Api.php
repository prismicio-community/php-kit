<?php
declare(strict_types=1);

namespace Prismic;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Uri;
use Prismic\Cache\DefaultCache;
use Prismic\Document\Hydrator;
use Prismic\Document\HydratorInterface;
use Prismic\Exception\ExceptionInterface;
use Prismic\Exception\ExpiredPreviewTokenException;
use Prismic\Exception\InvalidArgumentException;
use Prismic\Exception\RequestFailureException;
use Prismic\Exception\RuntimeException;
use Psr\Cache\CacheException;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use function array_filter;
use function count;
use function filter_var;
use function md5;
use function parse_url;
use function preg_match;
use function sprintf;
use function str_replace;
use function strtolower;
use function urldecode;
use const FILTER_FLAG_PATH_REQUIRED;
use const FILTER_VALIDATE_URL;

/**
 * This class embodies a connection to your prismic.io repository's API.
 * Initialize it with Prismic::Api::get(), and use your Prismic::Api::forms() to make API calls
 * (read more in <a href="https://github.com/prismicio/php-kit">the kit's README file</a>)
 */
class Api
{
    private const API_VERSION_1 = '1.0.0';

    private const API_VERSION_2 = '2.0.0';

    /**
     * Name of the cookie that will be used to remember the preview reference
     */
    public const PREVIEW_COOKIE = 'io.prismic.preview';

    /**
     * Name of the cookie that will be used to remember the experiment reference
     */
    public const EXPERIMENTS_COOKIE = 'io.prismic.experiment';

    /** @var string|null */
    private $accessToken;

    /** @var string */
    private $url;

    /** @var CacheItemPoolInterface */
    private $cache;

    /** @var ClientInterface */
    private $httpClient;

    /** @var HydratorInterface */
    private $hydrator;

    /**
     * The API version determined by the URL passed to the named constructor
     *
     * @var string
     */
    private $version;

    /** @var LinkResolver|null */
    private $linkResolver;

    /** @var SearchFormCollection|null */
    private $forms;

    /**
     * Request cookies to inspect for preview or experiment refs
     *
     * By default, this array is populated with the $_COOKIE super global but can be overridden with setRequestCookies()
     *
     * @var string[]
     */
    private $requestCookies;

    private function __construct()
    {
        $this->requestCookies = $_COOKIE ?? [];
    }

    /**
     * This is the factory method with which to retrieve your API client instance
     *
     * If your API is set to "public" or "open", you can instantiate your Api object just like this:
     * Api::get('https://your-repository-name.prismic.io/api/v2')
     *
     * @param string                 $action      The URL of your repository API's endpoint
     * @param string                 $accessToken A permanent access token to use if your repository API is set to private
     * @param ClientInterface        $httpClient  Custom Guzzle http client
     * @param CacheItemPoolInterface $cache       Cache implementation
     */
    public static function get(
        string $action,
        ?string $accessToken = null,
        ?ClientInterface $httpClient = null,
        ?CacheItemPoolInterface $cache = null
    ) : self {
        $api = new static();
        $api->accessToken = $accessToken === '' ? null : $accessToken;
        $api->url = $action;
        $api->httpClient = $httpClient ?? new Client();
        $api->cache = $cache ?? DefaultCache::factory();

        $api->version = preg_match('~/v2$~i', $action) ? self::API_VERSION_2 : self::API_VERSION_1;

        $api->setHydrator(new Hydrator($api, [], Document::class));

        return $api;
    }

    private function apiDataUrl() : string
    {
        $url = new Uri($this->url);
        $url = $this->accessToken
            ? Uri::withQueryValue($url, 'access_token', $this->accessToken)
            : $url;

        return (string) $url;
    }

    private function apiDataCacheItem() : CacheItemInterface
    {
        $url = $this->apiDataUrl();
        $key = static::generateCacheKey($url);
        try {
            return $this->cache->getItem($key);
        } catch (CacheException $cacheException) {
            throw new RuntimeException(
                'A cache exception occurred whilst retrieving cached api data',
                0,
                $cacheException
            );
        }
    }

    private function getApiData() : ApiData
    {
        $url = $this->apiDataUrl();
        try {
            $response = $this->httpClient->request('GET', $url);

            return ApiData::withJsonString((string) $response->getBody());
        } catch (GuzzleException $guzzleException) {
            throw RequestFailureException::fromGuzzleException($guzzleException);
        }
    }

    public function getHydrator() : HydratorInterface
    {
        return $this->hydrator;
    }

    public function setHydrator(HydratorInterface $hydrator) : void
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

    /**
     * Set cookie values found in the request
     *
     * If preview and experiment cookie values are not available in your environment in the $_COOKIE super global, you
     * can provide them here and they'll be inspected to see if a preview is required or an experiment is running
     *
     * @param string[] $cookies
     */
    public function setRequestCookies(array $cookies) : void
    {
        $this->requestCookies = $cookies;
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
        return $this->version === self::API_VERSION_1;
    }

    /**
     * Returns all of the repository's references (queryable points in time)
     *
     * @return Ref[]
     */
    public function refs() : array
    {
        $groupBy = [];
        foreach ($this->getData()->getRefs() as $ref) {
            $label = $ref->getLabel();
            if (isset($groupBy[$label])) {
                continue;
            }

            $groupBy[$label] = $ref;
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
     * @return string[] the array of bookmarks
     */
    public function bookmarks() : array
    {
        return $this->getData()->getBookmarks();
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

        return $bookmarks[$name] ?? null;
    }

    /**
     * Returns the master ref repository: the ref which is to be used to query content
     * that is live right now.
     *
     * @return Ref the master ref
     */
    public function master() : Ref
    {
        $masters = array_filter($this->getData()->getRefs(), static function (Ref $ref) : bool {
            return $ref->isMasterRef() === true;
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
            foreach ($this->getData()->getForms() as $form) {
                $forms[$form->getKey()] = new SearchForm($this, $form, $form->defaultData());
            }

            $this->forms = new SearchFormCollection($forms);
        }

        return $this->forms;
    }

    public function getExperiments() : Experiments
    {
        return $this->getData()->getExperiments();
    }

    /**
     * Validate and filter a preview token ensuring that the URL it represents corresponds to the same host as the API
     */
    private function validatePreviewToken(string $token) : string
    {
        // Even if the token has already been decoded, if it's a reasonable url,
        // repeated decodes should not cause a problem.
        $token = urldecode($token);
        if (! filter_var($token, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED)) {
            throw new InvalidArgumentException(sprintf(
                'The preview token "%s" is not a valid url',
                $token
            ), 400);
        }

        ['host' => $previewHost] = parse_url($token);
        ['host' => $apiHost] = parse_url($this->url);
        /**
         * Because the API host will possibly be name.cdn.prismic.io but the preview domain can be name.prismic.io
         * we can only reliably verify the same parent domain name if we parse both domains with something that uses
         * the public suffix list, like https://github.com/jeremykendall/php-domain-parser for example. We really
         * don't want to have to go through all that, so for now we will just strip/hard-code the 'cdn' part which
         * causes the problem.
         */
        $previewHost = str_replace('.cdn.', '.', strtolower($previewHost));
        $apiHost = str_replace('.cdn.', '.', strtolower($apiHost));
        if ($previewHost !== $apiHost) {
            throw new InvalidArgumentException(sprintf(
                'The host "%s" does not match the api host "%s"',
                $previewHost,
                $apiHost
            ), 400);
        }

        return $token;
    }

    /**
     * Return the URL to display a given preview
     *
     * @param string $token      as received from Prismic server to identify the content to preview
     * @param string $defaultUrl the URL to return if the preview doesn't correspond to a document
     *
     * @return string the URL you should redirect the user to preview the requested change
     *
     * @throws ExceptionInterface If there's a problem querying the API.
     */
    public function previewSession(string $token, string $defaultUrl) : string
    {
        try {
            // $token is untrusted input, possibly from a GET request and will be retrieved by the http client
            $token = $this->validatePreviewToken($token);
            $response = $this->httpClient->request('GET', $token);
            $responseBody = Json::decodeObject((string) $response->getBody());
            if (isset($responseBody->mainDocument)) {
                $document = $this->getById(
                    $responseBody->mainDocument,
                    ['ref' => $token]
                );
                if ($document && $this->linkResolver) {
                    $url = $this->linkResolver->resolve($document->asLink());

                    return $url ?: $defaultUrl;
                }
            }

            return $defaultUrl;
        } catch (RequestException $requestException) {
            $apiResponse = $requestException->getResponse();
            if ($apiResponse && ExpiredPreviewTokenException::isTokenExpiryResponse($apiResponse)) {
                throw ExpiredPreviewTokenException::fromResponse($apiResponse);
            }

            throw RequestFailureException::fromGuzzleException($requestException);
        } catch (GuzzleException $exception) {
            throw RequestFailureException::fromGuzzleException($exception);
        }
    }

    /**
     * Accessing raw data returned by the /api endpoint
     */
    public function getData() : ApiData
    {
        $cacheItem = $this->apiDataCacheItem();
        if ($cacheItem->isHit()) {
            $data = $cacheItem->get();
            if ($data instanceof ApiData) {
                return $data;
            }
        }

        $data = $this->getApiData();
        $cacheItem->set($data);
        $this->cache->save($cacheItem);

        return $data;
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
            str_replace(['.', ' '], '_', self::PREVIEW_COOKIE),
            self::PREVIEW_COOKIE,
        ];
        foreach ($cookieNames as $cookieName) {
            if (isset($this->requestCookies[$cookieName])) {
                return $this->requestCookies[$cookieName];
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
            str_replace(['.', ' '], '_', self::EXPERIMENTS_COOKIE),
            self::EXPERIMENTS_COOKIE,
        ];
        foreach ($cookieNames as $cookieName) {
            if (isset($this->requestCookies[$cookieName])) {
                $experiments = $this->getExperiments();

                return $experiments->refFromCookie($this->requestCookies[$cookieName]);
            }
        }

        return null;
    }

    /**
     * Whether the current ref in use is a preview, i.e. the user is in preview mode
     */
    public function inPreview() : bool
    {
        return $this->getPreviewRef() !== null;
    }

    /**
     * Whether the current ref in use is an experiment
     */
    public function inExperiment() : bool
    {
        return $this->getExperimentRef() !== null && $this->inPreview() === false;
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
     * @param  string|string[]|Predicate[]|Predicate $q       the query, as a string, predicate or array of predicates
     * @param  mixed[]                               $options query options: pageSize, orderings, etc.
     *
     * @throws ExceptionInterface if parameters are invalid.
     */
    public function query($q, array $options = []) : Response
    {
        $options = $this->prepareDefaultQueryOptions($options);
        $ref = $this->ref();

        $form = $this->forms()->getForm('everything');
        if (! $form) {
            throw new RuntimeException('The form "everything" does not exist');
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
     * @param  string|string[]|Predicate[]|Predicate $q       the query, as a string, predicate or array of predicates
     * @param  mixed[]                               $options query options: pageSize, orderings, etc.
     *
     * @throws ExceptionInterface if parameters are invalid.
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
     * @param string  $id      The requested id
     * @param mixed[] $options Query options: pageSize, orderings, etc.
     *
     * @throws ExceptionInterface if parameters are invalid.
     */
    public function getById(string $id, array $options = []) :? DocumentInterface
    {
        return $this->queryFirst(Predicates::at('document.id', $id), $options);
    }

    /**
     * Search a document by its uid
     *
     * @param string  $type    The custom type of the requested document
     * @param string  $uid     The requested uid
     * @param mixed[] $options Query options: pageSize, orderings, etc.
     *
     * @throws ExceptionInterface if parameters are invalid.
     */
    public function getByUid(string $type, string $uid, array $options = []) :? DocumentInterface
    {
        return $this->queryFirst(
            Predicates::at(sprintf(
                'my.%s.uid',
                $type
            ), $uid),
            $options
        );
    }

    /**
     * Return a set of document from their ids
     *
     * @param string[] $ids     Array of strings, the requested ids
     * @param mixed[]  $options Query options: pageSize, orderings, etc.
     *
     * @throws ExceptionInterface if parameters are invalid.
     */
    public function getByIds(array $ids, array $options = []) : Response
    {
        return $this->query(Predicates::in('document.id', $ids), $options);
    }

    /**
     * Get a single typed document by its type
     *
     * @param string  $type    The custom type of the requested document
     * @param mixed[] $options Query options: pageSize, orderings, etc.
     *
     * @throws ExceptionInterface if parameters are invalid.
     */
    public function getSingle(string $type, array $options = []) :? DocumentInterface
    {
        return $this->queryFirst(Predicates::at('document.type', $type), $options);
    }

    /**
     * Given an options array for a query, fill the lang parameter with a default value
     *
     * @param mixed[] $options Query options: pageSize, orderings, etc.
     *
     * @return mixed[]
     */
    private function prepareDefaultQueryOptions(array $options) : array
    {
        if (! isset($options['lang'])) {
            $options['lang'] = '*';
        }

        return $options;
    }
}
