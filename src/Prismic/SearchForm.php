<?php
declare(strict_types=1);

namespace Prismic;

use Prismic\Exception;

/**
 * Embodies an API call we are in the process of building. This gets started with Prismic\Api.form,
 * then you can chain instance method calls to build your query, and the query gets launched with
 * Prismic\SearchForm.submit.
 *
 * For instance, here's how you query all of the repository:
 * $result = $api->form('everything')->ref($ref)->submit()
 *
 * And here's an example of a more complex query:
 * $result = $api->form('products')->query('[[:d = any(document.tags, ["Featured"])]]')->pageSize(10)->page(2)->ref($ref)->submit()
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
     * The API object containing all the information to know where to query
     * @var Api
     */
    private $api;

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
     * @param Prismic::Api  $api  the API object containing all the information to know where to query
     * @param Prismic::Form $form the REST form we're querying on in the API
     * @param array         $data the parameters we're getting ready to submit
     */
    public function __construct(Api $api, Form $form, array $data)
    {
        $this->api  = $api;
        $this->form = $form;
        $this->data = $data;
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
        $fields = $this->form->getFields();
        if ( ! isset($fields[$key])) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Unknown form field parameter "%s"',
                $key
            ));
        }

        /** @var FieldForm $field */
        $field = $fields[$key];

        if ($field->getType() === 'String' && !is_string($value)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'The field %s expects a string parameter, received %s',
                $key,
                gettype($value)
            ));
        }

        if ($field->getType() === 'Integer' && !is_numeric($value)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'The field %s expects an integer parameter, received %s',
                $key,
                gettype($value)
            ));
        }


        $data   = $this->data;
        if ($field->isMultiple()) {
            $values = isset($data[$key]) ? $data[$key] : [];
            if (is_array($values)) {
                array_push($values, $value);
            } else {
                $values = [$value];
            }
            $data[$key] = $values;
        } else {
            $data[$key] = $value;
        }

        return new self($this->api, $this->form, $data);
    }

    /**
     * Set the repository ref to query at
     *
     * @param  string|Ref $ref the ref we wish to query on, or its ID.
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
     */
    public function after(string $documentId) : self
    {
        return $this->set("after", $documentId);
    }

    /**
     * Set the fetch parameter: restrict the fields to retrieve for a document

     * You can pass in parameter an array of strings, or several strings.
     */
    public function fetch() : self
    {
        $numargs = func_num_args();
        if ($numargs === 1 && is_array(func_get_arg(0))) {
            $fields = func_get_arg(0);
        } else {
            $fields = func_get_args();
        }
        return $this->set("fetch", implode(",", $fields));
    }

    /**
     * Set the fetchLinks parameter: additional fields to retrieve for DocumentLink, You can pass in parameter
     * an array of strings, or several strings.
     */
    public function fetchLinks() : self
    {
        $numargs = func_num_args();
        if ($numargs === 1 && is_array(func_get_arg(0))) {
            $fields = func_get_arg(0);
        } else {
            $fields = func_get_args();
        }
        return $this->set("fetchLinks", join(",", $fields));
    }

    /**
     * Set the language for the query documents.
     */
    public function lang(string $lang) : self
    {
        return $this->set("lang", $lang);
    }

    /**
     * Set the query's page size, for the pagination.
     */
    public function pageSize(int $pageSize) : self
    {
        return $this->set("pageSize", $pageSize);
    }

    /**
     * Set the query result page number, for the pagination.
     */
    public function page(int $page) : self
    {
        return $this->set("page", $page);
    }

    /**
     * Set the query's ordering, setting in what order the documents must be retrieved.
     */
    public function orderings() : self
    {
        if (func_num_args() == 0) return $this;
        $orderings = "[" . implode(",", array_map(function($order) {
            return preg_replace('/(^\[|\]$)/', '', $order);
        }, func_get_args())) . "]";
        return $this->set("orderings", $orderings);
    }

    /**
     * Submit the current API call, and unmarshalls the result into PHP objects.
     */
    public function submit()
    {
        return $this->submit_raw();
    }

    /**
     * Get the result count for this form
     *
     * This uses a copy of the SearchForm with a page size of 1 (the smallest
     * allowed) since all we care about is one of the returned non-result
     * fields.
     *
     * @return int Total number of results
     *
     * \throws RuntimeException
     */
    public function count()
    {
        return $this->pageSize(1)->submit_raw()->total_results_size;
    }

    /**
     * Set the query's predicates themselves.
     * You can pass a String representing a query as parameter, or one or multiple Predicates to build an "AND" query
     */
    public function query() : self
    {
        $numargs = func_num_args();
        if ($numargs === 0) return clone $this;
        $first = func_get_arg(0);
        if ($numargs === 1 && is_string($first)) {
            return $this->set("q", $first);
        }
        if ($numargs === 1 && is_array($first)) {
            $predicates = $first;
        } else {
            $predicates = func_get_args();
        }
        $query = "[" . implode("", array_map(function($predicate) { return $predicate->q(); }, $predicates)) . "]";
        return $this->set("q", $query);
    }

    /**
     * Get the URL for this form
     */
    public function url() : string
    {
        $url = $this->form->getAction() . '?' . http_build_query($this->data);
        $url = preg_replace('/%5B(?:[0-9]|[1-9][0-9]+)%5D=/', '=', $url);
        return $url;
    }

    /**
     * Checks if the results for this form are already cached
     */
    public function isCached() : bool
    {
        return $this->api->getCache()->has($this->url());
    }

    /**
     * Performs the actual submit call, without the unmarshalling.
     *
     * @throws Exception\RuntimeException if the Form type is not supported
     *
     * @return the raw (unparsed) response.
     */
    private function submit_raw()
    {
        if ($this->form->getMethod() !== 'GET' ||
            $this->form->getEnctype() !== 'application/x-www-form-urlencoded' ||
            ! $this->form->getAction()
        ) {
            throw new Exception\RuntimeException("Form type not supported");
        }
        $url = $this->url();
        $cacheKey = $this->url();

        $response = $this->api->getCache()->get($cacheKey);

        if ($response) {
            return $response;
        }
        $response = $this->api->getHttpClient()->get($url);
        $cacheControl = $response->getHeader('Cache-Control')[0];
        $cacheDuration = null;
        if (preg_match('/^max-age\s*=\s*(\d+)$/', $cacheControl, $groups) == 1) {
            $cacheDuration = (int) $groups[1];
        }
        $json = json_decode($response->getBody());
        if (!isset($json)) {
            throw new Exception\RuntimeException("Unable to decode json response");
        }
        if ($cacheDuration !== null) {
            $expiration = $cacheDuration;
            $this->api->getCache()->set($cacheKey, $json, $expiration);
        }
        return $json;
    }

}
