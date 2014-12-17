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
use Prismic\WithFragments;

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
class DocumentLink extends WithFragments implements LinkInterface
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
     * Constructs a document link.
     *
     * @param string  $id       the ID of the linked document
     * @param string  $uid      the UID of the linked document (can be null)
     * @param string  $type     the type of the linked document
     * @param array   $tags     an array of strings which are the document's tags
     * @param string  $slug     the current slug of the document
     * @param array   $fragments the additional fragment data
     * @param boolean $isBroken returns true if the link is towards a document that is not live, for instance
     */
    public function __construct($id, $uid, $type, $tags, $slug, array $fragments, $isBroken)
    {
        parent::__construct($fragments);
        $this->id = $id;
        $this->uid = $uid;
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
     * @param \Prismic\LinkResolver $linkResolver the link resolver
     *
     * @return string the HTML version of the link
     */
    public function asHtml($linkResolver = NULL)
    {
        return '<a href="' . $this->getUrl($linkResolver) . '">' . $this->slug . '</a>';
    }

    /**
     * Parses a proper bit of unmarshaled JSON into a DocumentLink object.
     * This is used internally during the unmarshaling of API calls.
     *
     * @param \stdClass $json the raw JSON that needs to be transformed into native objects.
     *
     * @return DocumentLink the new object that was created form the JSON.
     */
    public static function parse($json)
    {
        $uid = isset($json->document->uid) ? $json->document->uid : null;
        $fragments = isset($json->document->data) ? WithFragments::parseFragments($json->document->data) : array();
        return new DocumentLink(
            $json->document->id,
            $uid,
            $json->document->type,
            isset($json->document->{'tags'}) ? $json->document->tags : null,
            $json->document->slug,
            $fragments,
            $json->isBroken
        );
    }

    /**
     * Returns the URL of the document we're linking to.
     * The linkResolver will be needed in this case, as we're linking to a document link,
     * which should be a URL of your website.
     * Read more about the link resolver at the very end of prismic.io's documentation.
     *
     * @api
     *
     * @param \Prismic\LinkResolver $linkResolver the link resolver (read prismic.io's API documentation to learn more)
     *
     * @return string the URL of the resource we're linking to online
     */
    public function getUrl($linkResolver = null)
    {
        return $linkResolver($this);
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
     * Returns the UID of the linked document.
     *
     * @api
     *
     * @return string|null the UID of the linked document
     */
    public function getUid()
    {
        return $this->uid;
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
