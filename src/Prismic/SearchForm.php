<?php

namespace Prismic;

class SearchForm
{
    private $api;
    private $form;
    private $data;

    public function __construct($api, $form, $data)
    {
        $this->api = $api;
        $this->form = $form;
        $this->data = $data;
    }

    public function ref($ref)
    {
        $data = $this->data;
        $data['ref'] = $ref;
        return new SearchForm($this->api, $this->form, $data);
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
            $response = WS::get($url);
            WSResponse::check($response);
            return self::parseResult($response->data);
        }
        else {
            throw new \Exception("Form type not supported");
        }
    }

    public function query($q)
    {
        $field = $this->form->fields->q;
        $maybeDefault = property_exists($field, "default") ? $field->default : null;
        $q1 = isset($maybeDefault) ? self::strip($maybeDefault) : "";
        $data = $this->data;
        $data['q'] = '[' . $q1 . self::strip($q) . ']';

        return new SearchForm($this->api, $this->form, $data);
    }

    public static function strip($str)
    {
        $trimmed = trim($str);
        $drop1 = substr($trimmed, 1, strlen($trimmed));
        $dropR1 = substr($drop1, 0, strlen($drop1) - 1);
        return $dropR1;
    }
}