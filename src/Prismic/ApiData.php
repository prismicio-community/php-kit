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
 * Embodies structured data that can be wished to be used while manipulating a prismic.io API.
 * This is not supposed to be used to call a repository's API, this is solely for manipulation purpose by the class Prismic\Api.
 */
class ApiData
{
    /**
     * @var array an array of the usable refs for this API
     */
    private $refs;
    /**
     * @var array an array of the available bookmarks
     */
    private $bookmarks;
    /**
     * @var array an array of the available types
     */
    private $types;
    /**
     * @var array an array of the available tags
     */
    private $tags;
    /**
     * @var array an array of the available forms
     */
    private $forms;
    /**
     * @var string the URL of the endpoint to initiate the OAuth authentication
     */
    private $oauth_initiate;
    /**
     * @var string the URL of the endpoint to authenticate through OAuth
     */
    private $oauth_token;
    /**
     * @var \Prismic\Experiments list of both drafts and running experiments from Prismic
     */
    private $experiments;

    /**
     * A constructor to build the object when you've retrieved all the data you need.
     *
     * @param array     $refs
     * @param array     $bookmarks
     * @param array     $types
     * @param array     $tags
     * @param array     $forms
     * @param \Prismic\Experiments $experiments
     * @param string    $oauth_initiate
     * @param string    $oauth_token
     */
    public function __construct(
        array $refs,
        array $bookmarks,
        array $types,
        array $tags,
        array $forms,
        Experiments $experiments,
        $oauth_initiate,
        $oauth_token
    ) {
        $this->refs = $refs;
        $this->bookmarks = $bookmarks;
        $this->types = $types;
        $this->tags = $tags;
        $this->forms = $forms;
        $this->experiments = $experiments;
        $this->oauth_initiate = $oauth_initiate;
        $this->oauth_token = $oauth_token;
    }

    /**
     * Get the refs
     *
     * @return array
     */
    public function getRefs()
    {
        return $this->refs;
    }

    /**
     * Get the bookmarks
     *
     * @return array
     */
    public function getBookmarks()
    {
        return $this->bookmarks;
    }

    /**
     * Get the types
     *
     * @return array
     */
    public function getTypes()
    {
        return $this->types;
    }

    /**
     * Get the tags
     *
     * @return array
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Get the forms
     *
     * @return array
     */
    public function getForms()
    {
        return $this->forms;
    }

    public function getExperiments()
    {
        return $this->experiments;
    }

    /**
     * Get the endpoint to initiate OAuth
     *
     * @return string
     */
    public function getOauthInitiate()
    {
        return $this->oauth_initiate;
    }

    /**
     * Get the endpoint to run OAuth
     *
     * @return string
     */
    public function getOauthToken()
    {
        return $this->oauth_token;
    }
}
