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
 * This class embodies a file link; it is what is retrieved from the API when
 * a link is created towards a non-image file.
 * LinkInterface objects can be found in two occasions: as the "$link" variable of a HyperlinkSpan object
 * (which happens when the link is a hyperlink in a StructuredText fragment), or the LinkInterface
 * can also be its own fragment (e.g. for a "related" fragment, that links to a related document).
 */
class FileLink implements LinkInterface
{
    /**
     * @var string the URL of the resource we're linking to
     */
    private $url;
    /**
     * @var string the kind of resource it is (document, image, ...)
     */
    private $kind;
    /**
     * @var string the size of the resource, in bytes
     */
    private $size;
    /**
     * @var string the resource's original filename, in bytes
     */
    private $filename;

    /**
     * Constructs a file link.
     *
     * @param string $url      the URL of the resource we're linking to
     * @param string $kind     the kind of resource it is (document, image, ...)
     * @param string $size     the size of the resource, in bytes
     * @param string $filename the resource's original filename, in bytes
     */
    public function __construct($url, $kind, $size, $filename)
    {
        $this->url = $url;
        $this->kind = $kind;
        $this->size = $size;
        $this->filename = $filename;
    }

    /**
     * Builds an HTML version of the raw link, pointing to the right URL,
     * and with the resource's filename as the hypertext.
     *
     * @api
     *
     * @param \Prismic\LinkResolver $linkResolver the link resolver
     *
     * @return string the HTML version of the link
     */
    public function asHtml($linkResolver = null)
    {
        return '<a href="' . $this->url . '">' . $this->filename . '</a>';
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
     * Returns the URL of the resource we're linking to
     *
     * @param \Prismic\LinkResolver $linkResolver the link resolver (read prismic.io's API documentation to learn more)
     *
     * @api
     *
     * @return string the URL of the resource we're linking to
     */
    public function getUrl($linkResolver = null)
    {
        return $this->url;
    }

    /**
     * Returns the kind of resource it is (document, image, ...)
     *
     * @api
     *
     * @return string the kind of resource it is (document, image, ...)
     */
    public function getKind()
    {
        return $this->kind;
    }

    /**
     * Returns the size of the resource, in bytes
     *
     * @api
     *
     * @return string the size of the resource, in bytes
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Returns the resource's original filename, in bytes
     *
     * @api
     *
     * @return string the resource's original filename, in bytes
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Parses a proper bit of unmarshaled JSON into a FileLink object.
     * This is used internally during the unmarshaling of API calls.
     *
     * @param \stdClass $json the raw JSON that needs to be transformed into native objects.
     *
     * @return FileLink the new object that was created form the JSON.
     */
    public static function parse($json)
    {
        return new FileLink(
            $json->file->url,
            $json->file->kind,
            $json->file->size,
            $json->file->name
        );
    }
}
