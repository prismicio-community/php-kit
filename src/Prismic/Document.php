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

use DateTime;
use DateTimeImmutable;
use Prismic\Fragment\Color;
use Prismic\Fragment\Date;
use Prismic\Fragment\Timestamp;
use Prismic\Fragment\Embed;
use Prismic\Fragment\Image;
use Prismic\Fragment\Number;
use Prismic\Fragment\GeoPoint;
use Prismic\Fragment\ImageView;
use Prismic\Fragment\Link\DocumentLink;
use Prismic\Fragment\Link\FileLink;
use Prismic\Fragment\Link\ImageLink;
use Prismic\Fragment\Link\WebLink;
use Prismic\Fragment\StructuredText;
use Prismic\Fragment\Text;
use Prismic\Fragment\Group;
use Prismic\Fragment\SliceZone;
use Prismic\AlternateLanguage;

/**
 * Embodies a document retrieved from the API, which we'll be able to manipulate.
 */
class Document extends WithFragments
{

    //! string the ID of the document (please use instance methods to get information that is in there)
    private $id;
    //! string the user ID of the document (please use instance methods to get information that is in there)
    private $uid;
    //! string the type of the document (please use instance methods to get information that is in there)
    private $type;
    //! string the URL of the document in the repository's API (please use instance methods to get information that is in there)
    private $href;
    //! array the tags used in the document (please use instance methods to get information that is in there)
    private $tags;
    //! array the slugs used in the document, in the past and today; today's slug is the head (please use instance methods to get information that is in there)
    private $slugs;
    //! string the language code of the document (please use instance methods to get information that is in there)
    private $lang;
    //! array the alternative language versions of the document (please use instance methods to get information that is in there)
    private $alternateLanguages;
    //! the raw json retrieved from the server
    private $data;

    /**
     * Constructs a Document object. To be used only for testing purposes, as this gets done during the unmarshalling
     *
     * @param string      $id                   the ID of the document
     * @param string|null $uid                  the user ID of the document
     * @param string      $type                 the type of the document
     * @param string      $href                 the URL of the document in the repository's API
     * @param array       $tags                 the tags used in the document
     * @param array       $slugs                the slugs used in the document, in the past and today; today's slug is the head
     * @param string      $lang                 the language code of the document
     * @param array       $alternateLanguages   the alternate language versions of the document
     * @param json        $data                 the raw json retrieved from the server
     * @param array       $fragments            all the fragments in the document
     */
    public function __construct($id, $uid, $type, $href, array $tags, array $slugs, $lang, array $alternateLanguages, array $fragments, $data)
    {
        parent::__construct($fragments);
        $this->id = $id;
        $this->uid = $uid;
        $this->type = $type;
        $this->href = $href;
        $this->tags = $tags;
        $this->slugs = $slugs;
        $this->lang = $lang;
        $this->alternateLanguages = $alternateLanguages;
        $this->data = $data;
    }

    /**
     * Returns the current slug of the document
     *
     * @return string|null the current slug of the document
     */
    public function getSlug()
    {
        if (count($this->slugs) > 0) {
            return $this->slugs[0];
        }

        return null;
    }

    /**
     * Checks if a given slug is a past or current slug of the document
     *
     * @param  string  $slug the slug to check
     *
     * @return boolean true if the slug is a past or current slug of the document, false otherwise
     */
    public function containsSlug($slug)
    {
        $found = array_filter($this->slugs, function ($s) use ($slug) {
            return $s == $slug;
        });

        return count($found) > 0;
    }


    /**
     * Returns the ID of the document
     *
     * @return string the ID of the document
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the user ID of the document, a unique but human-readable identifier
     * typically to be used in URLs.
     *
     * It can be null, if the uid is not declared in the document mask.
     *
     * @return string the ID of the document
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * Returns the type of the document
     *
     * @return string the type of the document
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Returns the URL of the document in the repository's API
     *
     * @return string the URL of the document in the repository's API
     */
    public function getHref()
    {
        return $this->href;
    }

    /**
     * Returns the tags in the document
     *
     * @return array the tags in the document
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Returns the slugs used in the document, in the past and today; today's slug is the head.
     * Your can use getSlug() if you need just the current slug.
     *
     * @return array the slugs used in the document, in the past and today; today's slug is the head
     */
    public function getSlugs()
    {
        return $this->slugs;
    }

    /**
     * Return the language code of the document
     *
     * It can be null, if the i18n feature is not enabled on the repository.
     *
     * @return string the language code of the document
     */
    public function getLang()
    {
        return $this->lang;
    }
    
    /**
     * Return the alternative language versions of this document
     * 
     * It can be an empty array, if the i18n feature is not enabled on the repository or there are no alternative versions published.
     *
     * @return array
     */
    public function getAlternateLanguages()
    {
        return $this->alternateLanguages;
    }
    
    /**
     * Return the specified alternate language version of this document
     * and null if the document doesn't exist
     * 
     * @param string $lang language code of the alternate language version, like "en-us" 
     * 
     * @return AlternateLanguage the directly usable object, or null if the alternate language version does not exist
     */
    public function getAlternateLanguage($lang)
    {
        foreach ($this->alternateLanguages as $alternateLanguage) {
            if ($alternateLanguage->getLang() == $lang) {
                return $alternateLanguage;
            }
        }
        return null;
    }

    /**
     * Returns the raw json data received from the server.
     * Most of the time it's better to use a different helper, but it can be useful for example
     * for newer features not yet integrated in the kit
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Return the DateTime this document was first published
     * This property will be null if the document was first published before the feature was released.
     *
     * @return DateTime
     */
    public function getFirstPublicationDate()
    {
        if (isset($this->data->first_publication_date) && null !== $this->data->first_publication_date) {
            return DateTimeImmutable::createFromFormat(DateTime::ISO8601, $this->data->first_publication_date);
        }
        return null;
    }

    /**
     * Return the DateTime this document was last published
     * This property will be null if the document was last published before the feature was released.
     *
     * @return DateTime
     */
    public function getLastPublicationDate()
    {
        if (isset($this->data->last_publication_date) && null !== $this->data->last_publication_date) {
            return DateTimeImmutable::createFromFormat(DateTime::ISO8601, $this->data->last_publication_date);
        }
        return null;
    }

    /**
     * Convert the document to a DocumentLink
     *
     * @return DocumentLink the newly created DocumentLink
     */
    public function asDocumentLink()
    {
        return new DocumentLink($this->id, $this->uid, $this->type, $this->tags, $this->getSlug(), $this->lang, $this->getFragments(), false);
    }

    /**
     * Parses a given document. Not meant to be used except for testing.
     *
     * @param  $json the json bit retrieved from the API that represents a document.
     *
     * @return Document the manipulable object for that document.
     */
    public static function parse(\stdClass $json)
    {
        $uid = isset($json->uid) ? $json->uid : null;

        $lang = isset($json->lang) ? $json->lang : null;
        
        $alternateLanguages = array();
        if (isset($json->alternate_languages)) {
            foreach ($json->alternate_languages as $alternateLanguage) {
                $alternateLanguages[] = AlternateLanguage::parse($alternateLanguage);
            }
        }

        $fragments = WithFragments::parseFragments($json->data);

        $slugs = array();
        foreach ($json->slugs as $slug) {
            $slugs[] = urldecode($slug);
        }

        return new Document(
            $json->id,
            $uid,
            $json->type,
            $json->href,
            $json->tags,
            $slugs,
            $lang,
            $alternateLanguages,
            $fragments,
            $json
        );
    }
}
