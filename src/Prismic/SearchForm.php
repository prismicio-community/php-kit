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

    /**
     * Set the repository reference
     *
     * @param string $ref
     *
     * @return SearchForm
     */
    public function ref($ref)
    {
        $data = $this->data;
        $data['ref'] = $ref;

        return new SearchForm($this->api, $this->form, $data);
    }

    /**
     * Create documents from the search results
     *
     * @param $results
     *
     * @return array
     */
    private static function parseResult($results)
    {
        return array_map(function ($json) {
            return Document::parse($json);
        }, $results);
    }

    /**
     * Submit the current form to retrieve remote contents
     *
     * @return stdClass
     *
     * @throws \RuntimeException
     */
    public function submit()
    {
        if ($this->form->method == 'GET' && $this->form->enctype == 'application/x-www-form-urlencoded' && $this->form->action) {
            $url = $this->form->action . '?' . http_build_query($this->data);

            // @todo: refactor this
            $request = Api::getClient()->get($url);
            $response = $request->send();

            $response = @json_decode($response->getBody(true));

            if (!$response) {
                throw new \RuntimeException("Unable to decode json response");
            }

            return self::parseResult($response);
        }

        throw new \RuntimeException("Form type not supported");
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
        $field = $this->form->fields->q;

        $maybeDefault = property_exists($field, "default") ? $field->default : null;
        $q1 = $maybeDefault ? self::strip($maybeDefault) : "";

        $data = $this->data;
        $data['q'] = '[' . $q1 . self::strip($q) . ']';

        return new SearchForm($this->api, $this->form, $data);
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