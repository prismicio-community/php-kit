<?php

namespace Prismic;

class Api
{
    private $data;
    private $maybeAccessToken;

    /**
     * @param string $data
     * @param null   $maybeAccessToken
     */
    public function __construct($data, $maybeAccessToken = null)
    {
        $this->data = $data;
        $this->maybeAccessToken = $maybeAccessToken;
    }

    public function refs()
    {
        $refs = $this->data->refs;
        $groupBy = array();
        foreach ($refs as $ref) {
            if (isset($refs[$ref->label])) {
                $arr = $refs[$ref->label];
                array_push($arr, $ref);
                $groupBy[$ref->label] = $arr;
            }
            else {
                $groupBy[$ref->label] = array($ref);
            }
        }

        $results = array();
        foreach ($groupBy as $label => $values) {
            $results[$label] = $values[0];
        }
        return $results;
    }

    public function bookmarks()
    {
        return $this->data->bookmarks;
    }

    public function master()
    {
        $masters = array_filter($this->data->refs, function ($ref) {
            return $ref->isMasterRef == true;
        });

        return $masters[0];
    }

    public function forms()
    {
        $forms = $this->data->forms;
        foreach ($forms as $key => $form) {
            $f = new Form(
                isset($form->name) ? $form->name : null,
                $form->method,
                isset($form->rel) ? $form->rel : null,
                $form->enctype,
                $form->action,
                $form->fields
            );
            $data = $f->defaultData();
            if (isset($this->maybeAccessToken)) {
                $data['access_token'] = $this->maybeAccessToken;
            }
            $forms->$key = new SearchForm($this, $f, $data);
        }
        return $forms;
    }

    public function oauthInitiateEndpoint()
    {
        return $this->data->oauth_initiate;
    }

    public function oauthTokenEndpoint()
    {
        return $this->data->oauth_token;
    }

    public static function get($url, $maybeAccessToken = null)
    {
        $paramToken = isset($maybeAccessToken) ? '?access_token=' . $maybeAccessToken : '';
        $response = WS::get($url . $paramToken);
        WSResponse::check($response);

        $apiData = new ApiData(
            array_map(function ($ref) {
                return Ref::parse($ref);
            }, $response->data->refs),
            $response->data->bookmarks,
            $response->data->types,
            $response->data->tags,
            $response->data->forms,
            $response->data->oauth_initiate,
            $response->data->oauth_token
        );

        return new API($apiData, $maybeAccessToken);
    }
}