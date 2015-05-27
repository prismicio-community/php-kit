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
 * @api
 */
class SearchForm
{
    /**
     * @var Prismic\Api the API object containing all the information to know where to query
     */
    private $api;
    /**
     * @var Prismic\Form the REST form we're querying on in the API
     */
    private $form;
    /**
     * @var array the parameters we're getting ready to submit
     */
    private $data;

    /**
     * Constructs a SearchForm object
     * @param \Prismic\Api  $api  the API object containing all the information to know where to query
     * @param \Prismic\Form $form the REST form we're querying on in the API
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
     *
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Sets a value for a given parameter. For instance: set('orderings', '[product.price]'),
     * or set('page', 2).
     *
     * Checks that the parameter is expected in the RESTful form before allowing to add it.
     *
     * @api
     * @param  string $key the name of the parameter
     * @param  string $value the value of the parameter
     * @throws \RuntimeException
     * @return \Prismic\SearchForm a clone of the SearchForm object with the new parameter added
     */
    public function set($key, $value)
    {
        if (isset($key) && isset($value)) {
            $fields = $this->form->getFields();
            $field = $fields[$key];

            if (is_int($value) && $field->getType() != "Integer") {
                throw new \RuntimeException("Cannot use a Int as value for field " . $key);
            } else {
                $data = $this->data;
                if ($field->isMultiple()) {
                    $values = isset($data[$key]) ? $data[$key] : array();
                    if (is_array($values)) {
                        array_push($values, $value);
                    } else {
                        $values = array($value);
                    }
                    $data[$key] = $values;
                } else {
                    $data[$key] = $value;
                }
            }
        } else {
            $data = $this->data;
        }

        return new SearchForm($this->api, $this->form, $data);
    }

    /**
     * Set the repository's ref.
     *
     * @api
     * @param  string|\Prismic\Ref $ref the ref we wish to query on, or its ID.
     * @return \Prismic\SearchForm a clone of the SearchForm object with the new ref parameter added
     */
    public function ref($ref)
    {
        if ($ref instanceof \Prismic\Ref) {
            $ref = $ref->getRef();
        }
        return $this->set("ref", $ref);
    }

    /**
     * Set the after parameter: the id of the document to start the results from (excluding that document).
     *
     * @api
     * @param  string            $documentId
     * @return \Prismic\SearchForm a clone of the SearchForm object with the new after parameter added
     */
    public function after($documentId)
    {
        return $this->set("after", $documentId);
    }

    /**
     * Set the fetch parameter: restrict the fields to retrieve for a document. You can pass in parameter
     * an array of strings, or several strings.
     *
     * @api
     * @return \Prismic\SearchForm a clone of the SearchForm object with the new fetch parameter added
     */
    public function fetch()
    {
        $numargs = func_num_args();
        if ($numargs == 1 && is_array(func_get_arg(0))) {
            $fields = func_get_arg(0);
        } else {
            $fields = func_get_args();
        }
        return $this->set("fetch", join(",", $fields));
    }

    /**
     * Set the fetchLinks parameter: additional fields to retrieve for DocumentLink, You can pass in parameter
     * an array of strings, or several strings.
     *
     * @api
     * @return \Prismic\SearchForm a clone of the SearchForm object with the new fetchLinks parameter added
     */
    public function fetchLinks()
    {
        $numargs = func_num_args();
        if ($numargs == 1 && is_array(func_get_arg(0))) {
            $fields = func_get_arg(0);
        } else {
            $fields = func_get_args();
        }
        return $this->set("fetchLinks", join(",", $fields));
    }

    /**
     * Set the query's page size, for the pagination.
     *
     * @api
     * @param  int                 $pageSize
     * @return \Prismic\SearchForm a clone of the SearchForm object with the new pageSize parameter added
     */
    public function pageSize($pageSize)
    {
        return $this->set("pageSize", $pageSize);
    }

    /**
     * Set the query's page, for the pagination.
     *
     * @api
     * @param  int                 $page
     * @return \Prismic\SearchForm a clone of the SearchForm object with the new page parameter added
     */
    public function page($page)
    {
        return $this->set("page", $page);
    }

    /**
     * Set the query's ordering, setting in what order the documents must be retrieved.
     *
     * @api
     * @param  string              $orderings
     * @return \Prismic\SearchForm a clone of the SearchForm object with the new orderings parameter added
     */
    public function orderings()
    {
        if (func_num_args() == 0) return $this;
        $orderings = "[" . join(",", array_map(function($order) { return preg_replace('/(^\[|\]$)/', '', $order); }, func_get_args())) . "]";
        return $this->set("orderings", $orderings);
    }

    /**
     * Submit the current API call, and unmarshals the result into PHP objects.
     *
     * @return \Prismic\Documents the result of the call
     * @throws \RuntimeException
     */
    public function submit()
    {
        return Response::parse($this->submit_raw());
    }

    /**
     * Get the result count for this form
     *
     * This uses a copy of the SearchForm with a page size of 1 (the smallest
     * allowed) since all we care about is one of the returned non-result
     * fields.
     *
     * @return integer           Total number of results
     * @throws \RuntimeException
     */
    public function count()
    {
        return $this->pageSize(1)->submit_raw()->total_results_size;
    }

    /**
     * Set the query's predicates themselves.
     * You can pass a String representing a query as parameter, or one or multiple Predicates to build an "AND" query
     *
     * @return \Prismic\SearchForm a clone of the SearchForm object with the new predicate or predicates added
     */
    public function query()
    {
        $numargs = func_num_args();
        if ($numargs == 0) return clone $this;
        $first = func_get_arg(0);
        if ($numargs == 1 && is_string($first)) {
            return $this->set("q", $first);
        }
        if ($numargs == 1 && is_array($first)) {
            $predicates = $first;
        } else {
            $predicates = func_get_args();
        }
        $query = "[" . join("", array_map(function($predicate) { return $predicate->q(); }, $predicates)) . "]";
        return $this->set("q", $query);
    }

    /**
     * Get the URL for this form
     *
     * @return string the URL
     */
    public function url()
    {
        $url = $this->form->getAction() . '?' . http_build_query($this->data);
        $url = preg_replace('/%5B(?:[0-9]|[1-9][0-9]+)%5D=/', '=', $url);
        return $url;
    }

    /**
     * Checks if the results for this form are already cached
     *
     * @api
     *
     * @return boolean true if the results for this form are fresh in the cache, false otherwise
     */
    public function isCached()
    {
        return $this->api->getCache()->has($this->url());
    }

    /**
     * Performs the actual submit call, without the unmarshalling.
     *
     * @throws \RuntimeException if the Form type is not supported
     * @return \stdClass the raw (unparsed) response.
     */
    private function submit_raw()
    {
        if ($this->form->getMethod() == 'GET' &&
            $this->form->getEnctype() == 'application/x-www-form-urlencoded' &&
            $this->form->getAction()
        ) {
            $url = $this->url();
            $cacheKey = $this->url();

            $response = $this->api->getCache()->get($cacheKey);

            if ($response) {
                return $response;
            } else {
                $response = $this->api->getHttpAdapter()->get($url);
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
                    $this->api->getCache()->set($cacheKey, $json, $expiration);
                }

                return $json;
            }
        }

        throw new \RuntimeException("Form type not supported");
    }

}
