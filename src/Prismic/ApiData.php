<?php
declare(strict_types=1);

namespace Prismic;

use Prismic\Value\Language;
use stdClass;
use function array_map;
use function array_walk;

class ApiData
{
    /** @var Ref[] */
    private $refs;

    /** @var string[] */
    private $bookmarks;

    /** @var string[] */
    private $types;

    /** @var string[] */
    private $tags;

    /** @var Form[] */
    private $forms;

    /** @var string */
    private $oauthInitiateUrl;

    /** @var string */
    private $oauthTokenUrl;

    /** @var Experiments */
    private $experiments;

    /** @var Language[] */
    private $languages;

    /**
     * A constructor to build the object when you've retrieved all the data you need.
     *
     * @param Ref[]      $refs
     * @param string[]   $bookmarks
     * @param string[]   $types
     * @param string[]   $tags
     * @param stdClass[] $forms
     * @param Language[] $languages
     */
    private function __construct(
        array $refs,
        array $bookmarks,
        array $types,
        array $tags,
        array $forms,
        Experiments $experiments,
        iterable $languages,
        string $oauthInitiateUrl,
        string $oauthTokenUrl
    ) {
        $this->refs             = $refs;
        $this->bookmarks        = $bookmarks;
        $this->types            = $types;
        $this->tags             = $tags;
        $this->forms            = $forms;
        $this->experiments      = $experiments;
        $this->languages        = $languages;
        $this->oauthInitiateUrl = $oauthInitiateUrl;
        $this->oauthTokenUrl    = $oauthTokenUrl;
    }

    /**
     * Return a new ApiData instance from the given JSON string
     *
     * @throws Exception\JsonError
     */
    public static function withJsonString(string $json) : self
    {
        return static::withJsonObject(
            Json::decodeObject($json)
        );
    }

    private static function withJsonObject(object $json) : self
    {
        $experiments = isset($json->experiments)
                     ? Experiments::parse($json->experiments)
                     : Experiments::parse(new stdClass());

        $languages = isset($json->languages)
            ? array_map(static function (object $object) : Language {
                return Language::new($object->id, $object->name);
            }, $json->languages)
            : [];

        $formData = isset($json->forms) ? (array) $json->forms : [];
        $forms = [];
        array_walk($formData, static function (object $form, string $key) use (&$forms) : void {
            $forms[$key] = Form::withJsonObject($key, $form);
        });

        return new static(
            array_map(
                static function ($ref) {
                    return Ref::parse($ref);
                },
                $json->refs
            ),
            (array) $json->bookmarks,
            (array) $json->types,
            $json->tags,
            $forms,
            $experiments,
            $languages,
            $json->oauth_initiate,
            $json->oauth_token
        );
    }

    /** @return Ref[] */
    public function getRefs() : array
    {
        return $this->refs;
    }

    /** @return string[] */
    public function getBookmarks() : array
    {
        return $this->bookmarks;
    }

    /** @return string[] */
    public function getTypes() : array
    {
        return $this->types;
    }

    /** @return string[] */
    public function getTags() : array
    {
        return $this->tags;
    }

    /** @return Form[] */
    public function getForms() : array
    {
        return $this->forms;
    }

    public function getExperiments() : Experiments
    {
        return $this->experiments;
    }

    public function getOauthInitiate() : string
    {
        return $this->oauthInitiateUrl;
    }

    public function getOauthToken() : string
    {
        return $this->oauthTokenUrl;
    }

    /** @return Language[] */
    public function languages() : iterable
    {
        return $this->languages;
    }
}
