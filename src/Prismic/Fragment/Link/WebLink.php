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
 * This class embodies a web link; it is what is retrieved from the API when
 * a link is created towards a media file.
 * LinkInterface objects can be found in two occasions: as the "$link" variable of a HyperlinkSpan object
 * (which happens when the link is a hyperlink in a StructuredText fragment), or the LinkInterface
 * can also be its own fragment (e.g. for a "related" fragment, that links to a related document).
 */
class WebLink implements LinkInterface
{
    /**
     * @var string the URL of the resource we're linking to online
     */
    private $url;
    /**
     * @var string the content type, if known
     */
    private $maybeContentType;

    /**
     * Constructs a media link.
     *
     * @param string $url              the URL of the resource we're linking to online
     * @param string $maybeContentType the content type, if known
     */
    public function __construct($url, $maybeContentType = null)
    {
        $this->url = $url;
        $this->maybeContentType = $maybeContentType;
    }

    /**
     * Builds an HTML version of the raw link, pointing to the right URL,
     * and with the resource's URL as the hypertext.
     *
     * @api
     *
     * @param \Prismic\LinkResolver $linkResolver the link resolver
     *
     * @return string the HTML version of the link
     */
    public function asHtml($linkResolver = null)
    {
        return '<a href="' . $this->url . '">' . $this->url . '</a>';
    }

    /**
     * Builds an unformatted text version of the raw link: simply, the URL.
     *
     * @api
     *
     * @return string an unformatted text version of the raw link
     */
    public function asText()
    {
        return $this->getUrl();
    }

    /**
     * Returns the URL of the resource we're linking to online
     *
     * @param \Prismic\LinkResolver $linkResolver the link resolver (read prismic.io's API documentation to learn more)
     *
     * @api
     *
     * @return string the URL of the resource we're linking to online
     */
    public function getUrl($linkResolver = null)
    {
        return $this->url;
    }

    /**
     * Returns the content type, if known
     *
     * @api
     *
     * @return string the content type, if known
     */
    public function getContentType()
    {
        return $this->maybeContentType;
    }

    /**
     * Parses a proper bit of unmarshaled JSON into a WebLink object.
     * This is used internally during the unmarshaling of API calls.
     *
     * @param \stdClass $json the raw JSON that needs to be transformed into native objects.
     *
     * @return WebLink the new object that was created form the JSON.
     */
    public static function parse($json)
    {
        return new WebLink($json->url);
    }
}
