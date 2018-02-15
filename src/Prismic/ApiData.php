<?php
declare(strict_types=1);

namespace Prismic;

use stdClass;

/**
 * Embodies structured data that can be wished to be used while manipulating a prismic.io API.
 * This is not supposed to be used to call a repository's API, this is solely for manipulation purpose by the class Prismic::Api.
 */
class ApiData
{
    //! an array of the usable refs for this API
    private $refs;
    //! an array of the available bookmarks
    private $bookmarks;
    //! array an array of the available types
    private $types;
    //! array an array of the available tags
    private $tags;
    //! array an array of the available forms
    private $forms;
    //! string the URL of the endpoint to initiate the OAuth authentication
    private $oauth_initiate;
    //! @var string the URL of the endpoint to authenticate through OAuth
    private $oauth_token;
    //! @var Experiments list of both drafts and running experiments from Prismic
    private $experiments;

    /**
     * A constructor to build the object when you've retrieved all the data you need.
     *
     * @param array       $refs
     * @param array       $bookmarks
     * @param array       $types
     * @param array       $tags
     * @param array       $forms
     * @param Experiments $experiments
     * @param string      $oauth_initiate
     * @param string      $oauth_token
     */
    public function __construct(
        array $refs,
        array $bookmarks,
        array $types,
        array $tags,
        array $forms,
        Experiments $experiments,
        string $oauth_initiate,
        string $oauth_token
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
     * Return a new ApiData instance from the given JSON string
     */
    public static function withJsonString(string $json) : self
    {
        return self::withJsonObject(json_decode($json));
    }

    /**
     * Return a new ApiData instance from the given JSON decoded object
     */
    public static function withJsonObject(stdClass $json) : self
    {
        $experiments = isset($json->experiments)
                     ? Experiments::parse($json->experiments)
                     : new Experiments(array(), array());
        return new self(
            array_map(
                function ($ref) {
                    return Ref::parse($ref);
                },
                $json->refs
            ),
            (array)$json->bookmarks,
            (array)$json->types,
            $json->tags,
            (array)$json->forms,
            $experiments,
            $json->oauth_initiate,
            $json->oauth_token
        );
    }

    /**
     * Get the refs
     */
    public function getRefs() : array
    {
        return $this->refs;
    }

    /**
     * Get the bookmarks
     */
    public function getBookmarks() : array
    {
        return $this->bookmarks;
    }

    /**
     * Get the types
     */
    public function getTypes() : array
    {
        return $this->types;
    }

    /**
     * Get the tags
     */
    public function getTags() : array
    {
        return $this->tags;
    }

    /**
     * Get the forms
     */
    public function getForms() : array
    {
        return $this->forms;
    }

    /**
     * Get the Experiments
     */
    public function getExperiments() : Experiments
    {
        return $this->experiments;
    }

    /**
     * Get the endpoint to initiate OAuth
     */
    public function getOauthInitiate() : string
    {
        return $this->oauth_initiate;
    }

    /**
     * Get the endpoint to run OAuth
     */
    public function getOauthToken() : string
    {
        return $this->oauth_token;
    }
}
