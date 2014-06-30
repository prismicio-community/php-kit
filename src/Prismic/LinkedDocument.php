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
 * This class embodies the linked documents that are attached to a given documents.
 *
 * Each time prismic.io returns a document to a query, it also gathers in an array the
 * characteristics of all the documents it links to. These links can either be link fragments,
 * or hyperlinks in StructuredText fragments, ... anything within the current document.
 *
 * The purpose of this feature is to make it trivial to develop a content discovery feature
 * that has a "Linked in this article" box.
 *
 * @api
 */
class LinkedDocument
{
    /**
     * @var string the ID of the linked document
     */
    private $id;
    /**
     * @var string the slug of the linked document
     */
    private $slug;
    /**
     * @var string the type of the linked document
     */
    private $type;
    /**
     * @var string the ID of the linked document
     */
    private $tags;

    /**
     * Constructs a LinkedDocument object.
     *
     * @param string $id               the ID of the linked document
     * @param string $slug             the slug of the linked document
     * @param string $type             the type of the linked document
     * @param string $tags             the tags of the linked document
     */
    public function __construct($id, $slug, $type, $tags)
    {
        $this->id = $id;
        $this->slug = $slug;
        $this->type = $type;
        $this->tags = $tags;
    }

    /**
     * Returns the ID of the linked document
     *
     * @return string the ID of the linked document
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the slug of the linked document
     *
     * @return string the slug of the linked document
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Returns the type of the linked document
     *
     * @return string the type of the linked document
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Returns the tags of the linked document
     *
     * @return string the tags of the linked document
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Parses a linked document.
     *
     * @param  \stdClass    $json the json bit retrieved from the API that represents a linked document.
     * @return \Prismic\LinkedDocument the manipulable object for that linked document.
     */
    public static function parse($json)
    {
        return new LinkedDocument(
            $json->id,
            isset($json->{'slug'}) ? $json->slug : null,
            $json->type,
            $json->tags
        );
    }
}
