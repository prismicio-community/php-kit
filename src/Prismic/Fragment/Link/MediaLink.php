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
 * This class embodies a media link; it is what is retrieved from the API when
 * a link is created towards a media.
 * LinkInterface objects can be found in two occasions: as the "$link" variable of a HyperlinkSpan object
 * (which happens when the link is a hyperlink in a StructuredText fragment), or the LinkInterface
 * can also be its own fragment (e.g. for a "related" fragment, that links to a related document).
 */
class MediaLink implements LinkInterface
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
     * @var string the target of the link
     */
    private $target;
    /**
     * @var int the height of the image
     */
    private $height;
    /**
     * @var int the width of the image
     */
    private $width;

    /**
     * Constructs a file link.
     *
     * @param string $url      the URL of the resource we're linking to
     * @param string $kind     the kind of resource it is (document, image, ...)
     * @param string $size     the size of the resource, in bytes
     * @param string $filename the resource's original filename, in bytes
     * @param string $target   the target of the link
     * @param int    $height   the height of the image
     * @param int    $width    the width of the image
     */
    public function __construct($url, $kind, $size, $filename, $target, $height = null, $width = null)
    {
        $this->url = $url;
        $this->kind = $kind;
        $this->size = $size;
        $this->filename = $filename;
        $this->target = $target;
        $this->height = $height;
        $this->width = $width;
    }

    /**
     * Builds an HTML version of the raw link, pointing to the right URL,
     * and with the resource's filename as the hypertext.
     *
     * 
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
     * 
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
     * 
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
     * 
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
     * 
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
     * 
     *
     * @return string the resource's original filename, in bytes
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Returns the target of the link
     *
     * 
     *
     * @return string the target of the link
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * Returns the height of the image.
     *
     * 
     *
     * @return int the height of the image
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * Returns the width of the image.
     *
     * 
     *
     * @return int the width of the image
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Parses a proper bit of unmarshaled JSON into a MediaLink object.
     * This is used internally during the unmarshaling of API calls.
     *
     * @param \stdClass $json the raw JSON that needs to be transformed into native objects.
     *
     * @return MediaLink the new object that was created form the JSON.
     */
    public static function parse($json)
    {
        $target = property_exists($json, "target") ? $json->target : null;
        $height = property_exists($json, "height") ? $json->height : null;
        $width = property_exists($json, "width") ? $json->width : null;
        return new MediaLink(
            $json->url,
            $json->kind,
            $json->size,
            $json->name,
            $target,
            $height,
            $width
        );
    }
}
