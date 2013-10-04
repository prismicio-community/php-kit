<?php

namespace Prismic;

use Guzzle\Http\ClientInterface;

class SearchForm
{
    private $client;
    private $form;
    private $data;

    /**
     * @param ClientInterface $client
     * @param $form
     * @param $data
     */
    public function __construct(ClientInterface $client, $form, $data)
    {
        $this->client = $client;
        $this->form = $form;
        $this->data = $data;
    }

    public function ref($ref)
    {
        $data = $this->data;
        $data['ref'] = $ref;

        return new SearchForm($this->client, $this->form, $data);
    }

    private static function parseResult($result)
    {
        return array_map(function ($json) {
            return Document::parse($json);
        }, $result);
    }

    public function submit()
    {
        if ($this->form->method == 'GET' && $this->form->enctype == 'application/x-www-form-urlencoded' && $this->form->action) {
            $url = $this->form->action . '?' . http_build_query($this->data);

            // @todo: refactor this
            $request = $this->client->get($url);
            $response = $request->send();

            $response = @json_decode($response->getBody(true));

            return self::parseResult($response->data);
        }

        throw new \RuntimeException("Form type not supported");
    }

    public function query($q)
    {
        $field = $this->form->fields->q;
        $maybeDefault = property_exists($field, "default") ? $field->default : null;
        $q1 = isset($maybeDefault) ? self::strip($maybeDefault) : "";
        $data = $this->data;
        $data['q'] = '[' . $q1 . self::strip($q) . ']';

        return new SearchForm($this->client, $this->form, $data);
    }

    public static function strip($str)
    {
        $trimmed = trim($str);
        $drop1 = substr($trimmed, 1, strlen($trimmed));
        $dropR1 = substr($drop1, 0, strlen($drop1) - 1);

        return $dropR1;
    }
}