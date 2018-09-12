<?php
declare(strict_types=1);

namespace Prismic;

use Prismic\Exception;
use stdClass;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\ClientInterface;
use Prismic\Cache\CacheInterface;
use Prismic\Utils;

/**
 * Embodies an API call we are in the process of building. This gets started with Prismic\Api.form,
 * then you can chain instance method calls to build your query, and the query gets launched with
 * Prismic\SearchForm.submit.
 *
 * For instance, here's how you query all of the repository:
 * $result = $api->form('everything')->ref($ref)->submit()
 *
 * And here's an example of a more complex query:
 * $result = $api->form('products')
 *               ->query('[[:d = any(document.tags, ["Featured"])]]')->pageSize(10)->page(2)->ref($ref)->submit()
 *
 * Note that setting the ref is mandatory, or your submit call will fail.
 *
 * Note also that SearchForm objects are immutable; the chainable methods all
 * return new SearchForm objects.
 *
 */
class SearchForm
{
    /**
     * Cache Instance
     * @var CacheInterface
     */
    private $cache;

    /**
     * Http Client
     * @var ClientInterface
     */
    private $client;

    /**
     * The REST form we're querying on in the API
     * @var Form
     */
    private $form;

    /**
     * The parameters we're getting ready to submit
     * @var array
     */
    private $data;

    /**
     * Constructs a SearchForm object
     * @param ClientInterface $httpClient An HTTP Client for sending Requests
     * @param CacheInterface $cache A cache for storing responses
     * @param Form  $form the REST form we're querying on in the API
     * @param array $data the parameters we're getting ready to submit
     */
    public function __construct(ClientInterface $httpClient, CacheInterface $cache, Form $form, array $data)
    {
        $this->client = $httpClient;
        $this->cache  = $cache;
        $this->form   = $form;
        $this->data   = $data;
    }

    /**
     * Get the parameters we're about to submit.
     */
    public function getData() : array
    {
        return $this->data;
    }

    /**
     * Sets a value for a given parameter. For instance: set('orderings', '[product.price]'),
     * or set('page', 2).
     *
     * Checks that the parameter is expected in the RESTful form before allowing to add it.
     *
     * @param  string     $key the name of the parameter
     * @param  string|int $value the value of the parameter
     *
     * @throws Exception\ExceptionInterface
     *
     * @return self A clone of the SearchForm object with the new parameter added
     */
    public function set(string $key, $value) : self
    {
        if (empty($key)) {
            throw new Exception\InvalidArgumentException('Form parameter key must be a non-empty string');
        }
        if (! is_scalar($value)) {
            throw new Exception\InvalidArgumentException('Form parameter value must be scalar');
        }
        $fields = $this->form->getFields();
        if (! isset($fields[$key])) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Unknown form field parameter "%s"',
                $key
            ));
        }

        /** @var FieldForm $field */
        $field = $fields[$key];

        if ($field->getType() === 'String' && ! is_string($value)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'The field %s expects a string parameter, received %s',
                $key,
                gettype($value)
            ));
        }

        if ($field->getType() === 'Integer' && ! is_numeric($value)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'The field %s expects an integer parameter, received %s',
                $key,
                gettype($value)
            ));
        }


        $data = $this->data;
        if ($field->isMultiple()) {
            $data[$key] = isset($data[$key]) ? $data[$key] : [];
            $data[$key] = is_array($data[$key]) ? $data[$key] : [$data[$key]];
            $data[$key][] = $value;
        } else {
            $data[$key] = $value;
        }

        return new self($this->client, $this->cache, $this->form, $data);
    }

    /**
     * Set the repository ref to query at
     *
     * @param  string|Ref $ref the ref we wish to query on, or its ID.
     * @return self
     */
    public function ref($ref) : self
    {
        if ($ref instanceof Ref) {
            $ref = (string) $ref;
        }
        return $this->set("ref", $ref);
    }

    /**
     * Set the after parameter: the id of the document to start the results from (excluding that document).
     * @param string $documentId
     * @return self
     */
    public function after(string $documentId) : self
    {
        return $this->set("after", $documentId);
    }

    /**
     * Set the fetch parameter: restrict the fields to retrieve for a document
     *
     * Pass multiple string arguments or an array of strings to unpack with the splat operator
     *
     * @param string[] $fields
     * @return self
     */
    public function fetch(string ...$fields) : self
    {
        return $this->set("fetch", implode(",", $fields));
    }

    /**
     * Set the fetchLinks parameter: additional fields to retrieve for DocumentLink
     *
     * Pass multiple string arguments or an array of strings to unpack with the splat operator
     *
     * @param string[] $fields
     * @return self
     */
    public function fetchLinks(string ...$fields) : self
    {
        return $this->set("fetchLinks", join(",", $fields));
    }

    /**
     * Set the graphQuery parameter: GraphQL syntax based to select fields
     * @param string $query
     * @return self
     */
    public function graphQuery(string $query) : self
    {
        return $this->set("graphQuery", $query);
    }

    /**
     * Set the language for the query documents.
     * @param string $lang
     * @return self
     */
    public function lang(string $lang) : self
    {
        return $this->set("lang", $lang);
    }

    /**
     * Set the query's page size, for the pagination.
     * @param int $pageSize
     * @return self
     */
    public function pageSize(int $pageSize) : self
    {
        return $this->set("pageSize", $pageSize);
    }

    /**
     * Set the query result page number, for the pagination.
     * @param int $page
     * @return self
     */
    public function page(int $page) : self
    {
        return $this->set("page", $page);
    }

    /**
     * Set the query's ordering, setting in what order the documents must be retrieved.
     */
    public function orderings(string ...$fields) : self
    {
        $fields = array_filter($fields);
        if (empty($fields)) {
            return $this;
        }
        $orderings = "[" . implode(",", array_map(function ($order) {
            return preg_replace('/(^\[|\]$)/', '', $order);
        }, $fields)) . "]";
        return $this->set("orderings", $orderings);
    }

    /**
     * Submit the current API call, and unmarshalls the result into PHP objects.
     */
    public function submit() : stdClass
    {
        return $this->submitRaw();
    }

    /**
     * Get the result count for this form
     *
     * This uses a copy of the SearchForm with a page size of 1 (the smallest
     * allowed) since all we care about is one of the returned non-result
     * fields.
     */
    public function count() :? int
    {
        $response = $this->pageSize(1)->submitRaw();
        return isset($response->total_results_size)
               ? (int) $response->total_results_size
               : null;
    }

    /**
     * Set query predicates
     * You can provide a single string, or one or multiple Predicate instances to build an "AND" query
     */
    public function query(...$params) : self
    {
        // Filter empty args and return early if appropriate
        $params = array_filter($params);
        if (empty($params)) {
            return clone $this;
        }
        $first = current($params);
        // Unpack a single array argument
        if (count($params) === 1 && is_array($first)) {
            $params = $first;
        }
        $this->assertValidQueryParameters($params);
        if (count($params) === 1 && is_string($first)) {
            return $this->set("q", $first);
        }
        $query = "[" . implode("", array_map(function ($predicate) {
            /** @var Predicate $predicate */
            return $predicate->q();
        }, $params)) . "]";
        return $this->set("q", $query);
    }

    /**
     * Assert that the parameters used for a query contain either a single string, or an array of Predicates
     * @param array $params
     * @throws Exception\InvalidArgumentException
     */
    private function assertValidQueryParameters(array $params) : void
    {
        if (count($params) === 1 && is_string(current($params))) {
            return;
        }
        foreach ($params as $param) {
            if (! $param instanceof Predicate) {
                throw new Exception\InvalidArgumentException(
                    'Query parameters should consist of a single string or multiple Predicate instances'
                );
            }
        }
    }

    /**
     * Get the URL for this form
     */
    public function url() : string
    {
        return Utils::buildUrl($this->form->getAction(), $this->data);
    }

    /**
     * Checks if the results for this form are already cached
     */
    public function isCached() : bool
    {
        return $this->cache->has($this->url());
    }

    /**
     * Performs the actual submit call, without the unmarshalling.
     *
     * @throws Exception\RuntimeException if the Form type is not supported or the Response body is invalid
     * @throws Exception\RequestFailureException if something went wrong retrieving data from the API
     *
     * @return stdClass Unserialized JSON Response
     */
    private function submitRaw() : stdClass
    {
        if ($this->form->getMethod() !== 'GET' ||
            $this->form->getEnctype() !== 'application/x-www-form-urlencoded' ||
            ! $this->form->getAction()
        ) {
            throw new Exception\RuntimeException("Form type not supported");
        }
        $url = $this->url();
        $cacheKey = $this->url();

        $cachedJson = $this->cache->get($cacheKey);

        if ($cachedJson) {
            return $cachedJson;
        }
        try {
            /** @var \Psr\Http\Message\ResponseInterface $response */
            $response = $this->client->request('GET', $url);
        } catch (GuzzleException $guzzleException) {
            throw Exception\RequestFailureException::fromGuzzleException($guzzleException);
        }
        $cacheControl = $response->getHeader('Cache-Control')[0];
        $cacheDuration = null;
        if (preg_match('/^max-age\s*=\s*(\d+)$/', $cacheControl, $groups) == 1) {
            $cacheDuration = (int) $groups[1];
        }
        $json = \json_decode((string) $response->getBody());
        if (! isset($json)) {
            throw new Exception\RuntimeException("Unable to decode json response");
        }
        if ($cacheDuration !== null) {
            $this->cache->set($cacheKey, $json, $cacheDuration);
        }
        return $json;
    }
}
