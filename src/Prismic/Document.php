<?php
declare(strict_types=1);

namespace Prismic;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Prismic\Document\Fragment\FragmentCollection;
use Prismic\Document\Fragment\FragmentInterface;
use Prismic\Document\Fragment\Link\DocumentLink;
use Prismic\Exception\RuntimeException;
use stdClass;

class Document implements DocumentInterface
{

    /** @var string */
    protected $id;

    /**
     * @var string|null
     */
    protected $uid;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var array
     */
    protected $tags;

    /** @var array */
    protected $slugs;

    /**
     * @var DateTimeInterface|null
     */
    protected $firstPublished;

    /**
     * @var DateTimeInterface|null
     */
    protected $lastPublished;

    /**
     * @var string|null
     */
    protected $lang;

    /**
     * @var string
     */
    protected $href;

    /**
     * An array of Document Link pointing to translations of this document
     *
     * @var array
     */
    protected $alternateLanguages;

    /**
     * @var FragmentCollection
     */
    protected $data;

    /**
     * @var Api
     */
    protected $api;

    private function __construct()
    {
    }

    public static function fromJsonString(string $json, Api $api) : DocumentInterface
    {
        $data = \json_decode($json);
        if (! $data) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Failed to decode json payload: %s',
                \json_last_error_msg()
            ), \json_last_error());
        }
        return static::fromJsonObject($data, $api);
    }

    public static function fromJsonObject(stdClass $data, Api $api) : DocumentInterface
    {
        $inst        = new static;
        $inst->api   = $api;
        $inst->id    = $inst->assertRequiredProperty($data, 'id', false);
        $inst->uid   = $inst->assertRequiredProperty($data, 'uid', true);
        $inst->type  = $inst->assertRequiredProperty($data, 'type', false);
        $inst->tags  = $inst->assertRequiredProperty($data, 'tags', false);
        $inst->lang  = $inst->assertRequiredProperty($data, 'lang', true);
        $inst->href  = $inst->assertRequiredProperty($data, 'href', false);
        $inst->slugs = $inst->assertRequiredProperty($data, 'slugs', false);

        $utc            = new DateTimeZone('UTC');
        $firstPublished = $inst->assertRequiredProperty($data, 'first_publication_date', true);
        if ($firstPublished) {
            $date                 = DateTimeImmutable::createFromFormat(DateTime::ISO8601, $firstPublished);
            $inst->firstPublished = $date->setTimezone($utc);
        }
        $lastPublished = $inst->assertRequiredProperty($data, 'last_publication_date', true);
        if ($lastPublished) {
            $date                = DateTimeImmutable::createFromFormat(DateTime::ISO8601, $lastPublished);
            $inst->lastPublished = $date->setTimezone($utc);
        }

        $altLang                  = $inst->assertRequiredProperty($data, 'alternate_languages', true);
        $inst->alternateLanguages = $altLang ? $altLang : [];

        $data = $inst->assertRequiredProperty($data, 'data', false);

        /**
         * The root node in the data property is prefixed with the document type in the V1 API
         */
        $data = $api->isV1Api()
            ? $data->{$inst->type}
            : $data;

        if (! $api->getLinkResolver()) {
            throw new Exception\RuntimeException(
                'Documents cannot be properly hydrated without a Link Resolver being made available to the API'
            );
        }

        $inst->data = FragmentCollection::factory($data, $api->getLinkResolver());


        return $inst;
    }

    protected function assertRequiredProperty(stdClass $object, string $property, $nullable = true)
    {
        if (! \property_exists($object, $property)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'A required document property was missing from the JSON payload: %s',
                $property
            ));
        }
        $value = $object->{$property};
        if (null === $value && false === $nullable) {
            throw new Exception\InvalidArgumentException(sprintf(
                'A required document property was found to be null in the JSON payload: %s',
                $property
            ));
        }
        return $object->{$property};
    }

    public function getId() : string
    {
        return $this->id;
    }

    public function getUid() : ?string
    {
        return $this->uid;
    }

    public function getType() : string
    {
        return $this->type;
    }

    public function getTags() : array
    {
        return $this->tags;
    }

    public function getSlugs() : array
    {
        return $this->slugs;
    }

    public function getSlug() :? string
    {
        return count($this->slugs) ? current($this->slugs) : null;
    }

    public function getFirstPublicationDate() : ?DateTimeInterface
    {
        return $this->firstPublished;
    }

    public function getLastPublicationDate() : ?DateTimeInterface
    {
        return $this->lastPublished;
    }

    public function getLang() : ?string
    {
        return $this->lang;
    }

    public function getHref() : string
    {
        return $this->href;
    }

    public function getAlternateLanguages() : array
    {
        return $this->alternateLanguages;
    }

    public function getTranslation(string $lang) :? DocumentInterface
    {
        foreach ($this->alternateLanguages as $language) {
            if (isset($language->lang) && $language->lang === $lang) {
                $id = isset($language->id) ? (string) $language->id : null;
                return $id ? $this->api->getById($id) : null;
            }
        }
        return null;
    }

    public function getData() : FragmentCollection
    {
        return $this->data;
    }

    public function get(string $key) :? FragmentInterface
    {
        return $this->data->get($key);
    }

    public function has(string $key) : bool
    {
        return $this->data->has($key);
    }

    public function asLink() : DocumentLink
    {
        $resolver = $this->api->getLinkResolver();
        if (! $resolver) {
            throw new RuntimeException(
                'No link resolver has been defined so it is not possible to construct a link for this document'
            );
        }
        return DocumentLink::withDocument($this, $resolver);
    }
}
