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

/**
 * Embodies a document retrieved from the API, which we'll be able to manipulate.
 */
class Document extends WithFragments
{

    /**
     * @var string the ID of the document (please use instance methods to get information that is in there)
     */
    private $id;
    /**
     * @var string the user ID of the document (please use instance methods to get information that is in there)
     */
    private $uid;
    /**
     * @var string the type of the document (please use instance methods to get information that is in there)
     */
    private $type;
    /**
     * @var string the URL of the document in the repository's API (please use instance methods to get information that is in there)
     */
    private $href;
    /**
     * @var array the tags used in the document (please use instance methods to get information that is in there)
     */
    private $tags;
    /**
     * @var array the slugs used in the document, in the past and today; today's slug is the head (please use instance methods to get information that is in there)
     */
    private $slugs;

    /**
     * Constructs a Document object. To be used only for testing purposes, as this gets done during the unmarshalling
     *
     * @param string $id              the ID of the document
     * @param string|null $uid        the user ID of the document
     * @param string $type            the type of the document
     * @param string $href            the URL of the document in the repository's API
     * @param array  $tags            the tags used in the document
     * @param array  $slugs           the slugs used in the document, in the past and today; today's slug is the head
     * @param array  $fragments       all the fragments in the document
     */
    public function __construct($id, $uid, $type, $href, array $tags, array $slugs, array $fragments)
    {
        parent::__construct($fragments);
        $this->id = $id;
        $this->uid = $uid;
        $this->type = $type;
        $this->href = $href;
        $this->tags = $tags;
        $this->slugs = $slugs;
    }

    /**
     * Returns the current slug of the document
     *
     * @api
     *
     * @return string the current slug of the document
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
     * @api
     * @param  string  $slug the slug to check
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
     * @api
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
     * @api
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
     * @api
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
     * @api
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
     * @api
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
     * @api
     *
     * @return array the slugs used in the document, in the past and today; today's slug is the head
     */
    public function getSlugs()
    {
        return $this->slugs;
    }

    /**
     * Convert the document to a DocumentLink
     *
     * @return \Prismic\Fragment\Link\DocumentLink the newly created DocumentLink
     */
    public function asDocumentLink()
    {
        return new DocumentLink($this->id, $this->uid, $this->type, $this->tags, $this->getSlug(), $this->getFragments(), false);
    }

    /**
     * Parses a given document. Not meant to be used except for testing.
     *
     * @param  \stdClass         $json the json bit retrieved from the API that represents a document.
     * @return \Prismic\Document the manipulable object for that document.
     */
    public static function parse(\stdClass $json)
    {
        $uid = isset($json->uid) ? $json->uid : null;

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
            $fragments
        );
    }
}
