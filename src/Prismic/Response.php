<?php

namespace Prismic;

use Guzzle\Http\ClientInterface;

class Response
{
    protected $data;
    protected $maybeAccessToken;
    protected $client;

    /**
     * @param ClientInterface $client
     * @param string          $data
     * @param null            $maybeAccessToken
     */
    public function __construct(ClientInterface $client, $data, $maybeAccessToken = null)
    {
        $this->data = $data;
        $this->maybeAccessToken = $maybeAccessToken;
        $this->client = $client;
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
            } else {
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

            $forms->$key = new SearchForm($this->client, $f, $data);
        }

        return $forms;
    }

    /**
     * @return string
     */
    public function oauthInitiateEndpoint()
    {
        return $this->data->oauth_initiate;
    }

    /**
     * @return string
     */
    public function oauthTokenEndpoint()
    {
        return $this->data->oauth_token;
    }
}