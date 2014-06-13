<?php
/**
 * This file is part of the Prismic PHP SDK
 *
 * Copyright 2013 Zengularity (http://www.zengularity.com).
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Prismic\Fragment\Link;

/**
 * This class embodies a document link; it is what is retrieved from the API when
 * a link is created towards another document in the repository.
 * Do note that in those cases, you do not retrieve all of the linked documents,
 * but only enough information to build your link to the document. For this reason,
 * this is also the class of the object passed in the linkResolver function.
 * LinkInterface objects can be found in two occasions: as the "$link" variable of a HyperlinkSpan object
 * (which happens when the link is a hyperlink in a StructuredText fragment), or the LinkInterface
 * can also be its own fragment (e.g. for a "related" fragment, that links to a related document).
 */
class DocumentLink implements LinkInterface
{
    /**
     * @var string the ID of the linked document
     */
    private $id;
    /**
     * @var string the type of the linked document
     */
    private $type;
    /**
     * @var array an array of strings which are the document's tags
     */
    private $tags;
    /**
     * @var string the current slug of the document
     */
    private $slug;
    /**
     * @var boolean returns true if the link is towards a document that is not live, for instance
     */
    private $isBroken;

    /**
     * Constructs an document link.
     *
     * @param string   $id        the ID of the linked document
     * @param string   $type      the type of the linked document
     * @param array    $tags      an array of strings which are the document's tags
     * @param string   $slug      the current slug of the document
     * @param boolean  $isBroken  returns true if the link is towards a document that is not live, for instance
     */
    public function __construct($id, $type, $tags, $slug, $isBroken)
    {
        $this->id = $id;
        $this->type = $type;
        $this->tags = $tags;
        $this->slug = $slug;
        $this->isBroken = $isBroken;
    }

    /**
     * Builds an HTML version of the raw link, pointing to the right URL,
     * and with the document's slug as the hypertext.
     * If you want to use one of the document's fragments as the hypertext,
     * you will need to query the document in your controller.
     *
     * @api
     *
     * @param \Prismic\LinkResolver  $linkResolver  the link resolver
     *
     * @return string the HTML version of the link
     */
    public function asHtml($linkResolver)
    {
        return '<a href="' . $linkResolver($this) . '">' . $this->slug . '</a>';
    }

    /**
     * Parses a proper bit of unmarshaled JSON into a DocumentLink object.
     * This is used internally during the unmarshaling of API calls.
     *
     * @param \stdClass  $json  the raw JSON that needs to be transformed into native objects.
     *
     * @return DocumentLink  the new object that was created form the JSON.
     */
    public static function parse($json)
    {
        return new DocumentLink(
            $json->document->id,
            $json->document->type,
            isset($json->document->{'tags'}) ? $json->document->tags : null,
            $json->document->slug,
            $json->isBroken
        );
    }

    /**
     * Returns the ID of the linked document.
     *
     * @api
     *
     * @return string the ID of the linked document
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Builds an unformatted text version of the raw link: simply, the document's ID.
     *
     * @api
     *
     * @return string an unformatted text version of the raw link
     */
    public function asText()
    {
        return $this->id;
    }

    /**
     * Returns the type of the linked document
     *
     * @api
     *
     * @return string the type of the linked document
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Returns an array of strings which are the document's tags
     *
     * @api
     *
     * @return array an array of strings which are the document's tags
     */
    public function getTags()
    {
        return $this->tags;
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
        return $this->slug;
    }

    /**
     * Checks if the link is broken (towards a document that is not live, for instance)
     *
     * @api
     *
     * @return boolean true if the link is towards a document that is not live, for instance
     */
    public function isBroken()
    {
        return $this->isBroken;
    }
}
