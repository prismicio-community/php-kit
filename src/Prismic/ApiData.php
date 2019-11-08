<?php
declare(strict_types=1);

namespace Prismic;

use Prismic\Exception;
use stdClass;

class ApiData
{
    /**
     * An array of the usable refs for this API
     * @var array
     */
    private $refs;

    /**
     * An array of the available bookmarks
     * @var array
     */
    private $bookmarks;

    /**
     * An array of the available types
     * @var array
     */
    private $types;

    /**
     * An array of the available tags
     * @var array
     */
    private $tags;

    /**
     * An array of the available forms
     * @var array
     */
    private $forms;

    /**
     * The URL of the endpoint to initiate the OAuth authentication
     * @var string
     */
    private $oauth_initiate;

    /**
     * The URL of the endpoint to authenticate through OAuth
     * @var string
     */
    private $oauth_token;

    /**
     * List of both drafts and running experiments from Prismic
     * @var Experiments
     */
    private $experiments;

    /**
     * List of configured languages
     * @var array
     */
    private $languages;

    /**
     * A constructor to build the object when you've retrieved all the data you need.
     *
     * @param array $refs
     * @param array $bookmarks
     * @param array $types
     * @param array $languages
     * @param array $tags
     * @param array $forms
     * @param Experiments $experiments
     * @param string $oauth_initiate
     * @param string $oauth_token
     */
    private function __construct(
        array $refs,
        array $bookmarks,
        array $types,
        array $languages,
        array $tags,
        array $forms,
        Experiments $experiments,
        string $oauth_initiate,
        string $oauth_token
    ) {
        $this->refs = $refs;
        $this->bookmarks = $bookmarks;
        $this->types = $types;
        $this->languages = $languages;
        $this->tags = $tags;
        $this->forms = $forms;
        $this->experiments = $experiments;
        $this->oauth_initiate = $oauth_initiate;
        $this->oauth_token = $oauth_token;
    }

    /**
     * Return a new ApiData instance from the given JSON string
     * @param string $json
     * @return static
     */
    public static function withJsonString(string $json) : self
    {
        $data = json_decode($json);
        if (! $data) {
            throw new Exception\RuntimeException(sprintf(
                'Unable to decode JSON response: %s',
                json_last_error_msg()
            ), json_last_error());
        }
        return static::withJsonObject($data);
    }

    /**
     * Return a new ApiData instance from the given JSON decoded object
     * @param stdClass $json
     * @return self
     */
    public static function withJsonObject(stdClass $json) : self
    {
        $experiments = isset($json->experiments)
                     ? Experiments::parse($json->experiments)
                     : Experiments::parse(new stdClass);
        return new self(
            array_map(
                function ($ref) {
                    return Ref::parse($ref);
                },
                $json->refs
            ),
            (array)$json->bookmarks,
            (array)$json->types,
            array_map(
                function ($language) {
                    return Language::parse($language);
                },
                (array)$json->languages
            ),
            $json->tags,
            (array)$json->forms,
            $experiments,
            $json->oauth_initiate,
            $json->oauth_token
        );
    }

    /**
     * Get the refs
     * @return Ref[]
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

    public function getLanguages() : array
    {
        return $this->languages;
    }
}
