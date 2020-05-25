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
use Prismic\Exception\ExceptionInterface;
use Prismic\Exception\InvalidArgumentException;
use Prismic\Exception\JsonError;
use Prismic\Exception\RuntimeException;
use function assert;
use function count;
use function property_exists;
use function reset;
use function sprintf;

class Document implements DocumentInterface
{
    /** @var string */
    protected $id;

    /** @var string|null */
    protected $uid;

    /** @var string */
    protected $type;

    /** @var string[] */
    protected $tags;

    /** @var string[] */
    protected $slugs;

    /** @var DateTimeInterface|null */
    protected $firstPublished;

    /** @var DateTimeInterface|null */
    protected $lastPublished;

    /** @var string|null */
    protected $lang;

    /** @var string */
    protected $href;

    /**
     * An array of Document Link pointing to translations of this document
     *
     * @var object[]
     */
    protected $alternateLanguages;

    /** @var FragmentCollection */
    protected $data;

    /** @var Api */
    protected $api;

    /**
     * @param string[] $tags
     * @param string[] $slugs
     */
    private function __construct(
        string $id,
        string $type,
        array $tags,
        array $slugs,
        string $href,
        ?string $uid,
        ?string $lang
    ) {
        $this->id = $id;
        $this->type = $type;
        $this->tags = $tags;
        $this->slugs = $slugs;
        $this->href = $href;
        $this->uid = $uid;
        $this->lang = $lang;
    }

    /**
     * @throws JsonError If the payload is malformed.
     */
    public static function fromJsonString(string $json, Api $api) : DocumentInterface
    {
        return static::fromJsonObject(
            Json::decodeObject($json),
            $api
        );
    }

    public static function fromJsonObject(object $data, Api $api) : DocumentInterface
    {
        $inst = new static(
            static::assertRequiredProperty($data, 'id', false),
            static::assertRequiredProperty($data, 'type', false),
            static::assertRequiredProperty($data, 'tags', false),
            static::assertRequiredProperty($data, 'slugs', false),
            static::assertRequiredProperty($data, 'href', false),
            static::assertRequiredProperty($data, 'uid', true),
            static::assertRequiredProperty($data, 'lang', true)
        );
        $inst->api   = $api;

        $utc = new DateTimeZone('UTC');
        $firstPublished = static::assertRequiredProperty($data, 'first_publication_date', true);
        if ($firstPublished) {
            $date = DateTimeImmutable::createFromFormat(DateTime::ATOM, $firstPublished);
            $inst->firstPublished = $date->setTimezone($utc);
        }

        $lastPublished = static::assertRequiredProperty($data, 'last_publication_date', true);
        if ($lastPublished) {
            $date = DateTimeImmutable::createFromFormat(DateTime::ATOM, $lastPublished);
            $inst->lastPublished = $date->setTimezone($utc);
        }

        $altLang = static::assertRequiredProperty($data, 'alternate_languages', true);
        $inst->alternateLanguages = $altLang ?: [];

        $data = static::assertRequiredProperty($data, 'data', false);

        /**
         * The root node in the data property is prefixed with the document type in the V1 API
         */
        $data = $api->isV1Api()
            ? $data->{$inst->type}
            : $data;

        if (! $api->getLinkResolver()) {
            throw new RuntimeException(
                'Documents cannot be properly hydrated without a Link Resolver being made available to the API'
            );
        }

        $inst->data = FragmentCollection::factory($data, $api->getLinkResolver());

        return $inst;
    }

    /** @return mixed */
    protected static function assertRequiredProperty(object $object, string $property, bool $nullable = true)
    {
        if (! property_exists($object, $property)) {
            throw new InvalidArgumentException(sprintf(
                'A required document property was missing from the JSON payload: %s',
                $property
            ));
        }

        $value = $object->{$property};
        if ($value === null && $nullable === false) {
            throw new InvalidArgumentException(sprintf(
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

    /** @return string[] */
    public function getTags() : array
    {
        return $this->tags;
    }

    /** @return string[] */
    public function getSlugs() : array
    {
        return $this->slugs;
    }

    public function getSlug() :? string
    {
        return count($this->slugs) ? reset($this->slugs) : null;
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

    /** @return object[] */
    public function getAlternateLanguages() : array
    {
        return $this->alternateLanguages;
    }

    /**
     * Return a translation of this document for the given language if one exists
     *
     * @throws ExceptionInterface If a matching document is found but an error occurs retrieving it.
     */
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
        assert($resolver instanceof LinkResolver);

        return DocumentLink::withDocument($this, $resolver);
    }
}
