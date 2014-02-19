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

class ApiData
{
    private $refs;
    private $bookmarks;
    private $types;
    private $tags;
    private $forms;
    private $oauth_initiate;
    private $oauth_token;

    /**
     * @param array     $refs
     * @param \stdClass $bookmarks
     * @param \stdClass $types
     * @param array     $tags
     * @param \stdClass $forms
     * @param string    $oauth_initiate
     * @param string    $oauth_token
     */
    public function __construct(
        array $refs,
        \stdClass $bookmarks,
        \stdClass $types,
        array $tags,
        \stdClass $forms,
        $oauth_initiate,
        $oauth_token
    ) {
        $this->refs = $refs;
        $this->bookmarks = $bookmarks;
        $this->types = $types;
        $this->tags = $tags;
        $this->forms = $forms;
        $this->oauth_initiate = $oauth_initiate;
        $this->oauth_token = $oauth_token;
    }

    public function getRefs()
    {
        return $this->refs;
    }

    public function getBookmarks()
    {
        return $this->bookmarks;
    }

    public function getTypes()
    {
        return $this->types;
    }

    public function getTags()
    {
        return $this->tags;
    }

    public function getForms()
    {
        return $this->forms;
    }

    public function getOauthInitiate()
    {
        return $this->oauth_initiate;
    }

    public function getOauthToken()
    {
        return $this->oauth_token;
    }
}
