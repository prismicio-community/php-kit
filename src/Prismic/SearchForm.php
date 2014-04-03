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

class SearchForm
{
    private $api;
    private $form;
    private $data;

    /**
     * @param Api   $client
     * @param Form  $form
     * @param array $data
     */
    public function __construct(Api $api, Form $form, array $data)
    {
        $this->api  = $api;
        $this->form = $form;
        $this->data = $data;
    }

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

            return new SearchForm($this->api, $this->form, $data);
        } else {
            return null;
        }
    }

    /**
     * Set the repository reference
     *
     * @param string $ref
     *
     * @return SearchForm
     */
    public function ref($ref)
    {
        return $this->set("ref", $ref);
    }

    /**
     * Set the repository page size
     *
     * @param  int        $pageSize
     * @return SearchForm
     */
    public function pageSize($pageSize)
    {
        $data = $this->data;
        $data['pageSize'] = $pageSize;

        return new SearchForm($this->api, $this->form, $data);
    }

    /**
     * Set the repository page
     *
     * @param  int        $page
     * @return SearchForm
     */
    public function page($page)
    {
        $data = $this->data;
        $data['page'] = $page;

        return new SearchForm($this->api, $this->form, $data);
    }

    /**
     * Create documents from the search results
     *
     * @param $results
     *
     * @return array
     */
    private static function parseResult($json)
    {
        return array_map(function ($doc) {
            return Document::parse($doc);
        }, isset($json->results) ? $json->results : $json);
    }

    /**
     * Submit the current form to retrieve remote contents
     *
     * @return mixed Array of Document objects
     *
     * @throws \RuntimeException
     */
    public function submit()
    {
        return self::parseResult($this->submit_raw());
    }

    /**
     * Get the result count for this form
     *
     * This uses a copy of the SearchForm with a page size of 1 (the smallest
     * allowed) since all we care about is one of the returned non-result
     * fields.
     *
     * @return integer Total number of results
     *
     * @throws \RuntimeException
     */
    public function count()
    {
        return $this->pageSize(1)->submit_raw()->total_results_size;
    }

    /**
     * Generate a SearchForm instance for the provided query. Please note the ref method need to
     * be call before so the repository is set.
     *
     *    $boundForm = $formSearch->ref('my content repository reference');
     *    $queryForm = $boundForm->query('[[:d = at(document.type, "event")]]');
     *    $results = $queryForm->submit()
     *
     * @param $q
     *
     * @return SearchForm
     */
    public function query($q)
    {
        $fields = $this->form->getFields();
        $field = $fields['q'];
        if ($field->isMultiple()) {
            return $this->set("q", $q);
        } else {
            // Temporary Hack for backward compatibility
            $maybeDefault = property_exists($field, "defaultValue") ? $field->getDefaultValue() : null;
            $q1 = $maybeDefault ? self::strip($maybeDefault) : "";

            $data = $this->data;
            $data['q'] = '[' . $q1 . self::strip($q) . ']';

            return new SearchForm($this->api, $this->form, $data);
        }
    }

    /**
     * Perform the actual submit call
     *
     * @return the raw (unparsed) response
     */
    private function submit_raw()
    {
        if ($this->form->getMethod() == 'GET' &&
            $this->form->getEnctype() == 'application/x-www-form-urlencoded' &&
            $this->form->getAction()
        ) {
            $url = $this->form->getAction() . '?' . http_build_query($this->data);
            $url = preg_replace('/%5B(?:[0-9]|[1-9][0-9]+)%5D=/', '=', $url);

            $request = Api::defaultClient()->get($url);
            $response = $request->send();

            $response = @json_decode($response->getBody(true));
            if (!isset($response)) {
                throw new \RuntimeException("Unable to decode json response");
            }

            return $response;
        }

        throw new \RuntimeException("Form type not supported");
    }

    /**
     * Clean the query
     *
     * @param string $str
     *
     * @return string
     */
    private static function strip($str)
    {
        $trimmed = trim($str);
        $drop1 = substr($trimmed, 1, strlen($trimmed));
        $dropR1 = substr($drop1, 0, strlen($drop1) - 1);

        return $dropR1;
    }
}
