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
 * This class embodies an image link; it is what is retrieved from the API when
 * a link is created towards an image file.
 * LinkInterface objects can be found in two occasions: as the "$link" variable of a HyperlinkSpan object
 * (which happens when the link is a hyperlink in a StructuredText fragment), or the LinkInterface
 * can also be its own fragment (e.g. for a "related" fragment, that links to a related document).
 */
class ImageLink extends FileLink
{
    /**
     * @var integer the height of the image
     */
    private $height;
    /**
     * @var integer the width of the image
     */
    private $width;

    /**
     * Constructs an image link.
     *
     * @param string $url      the URL of the resource we're linking to
     * @param string $kind     the kind of resource it is (document, image, ...)
     * @param string $size     the size of the resource, in bytes
     * @param string $filename the resource's original filename, in bytes
     * @param string $height   the height of the image
     * @param string $width    the width of the image
     */
    public function __construct($url, $kind, $size, $filename, $height, $width)
    {
        parent::__construct($url, $kind, $size, $filename);
        $this->height = $height;
        $this->width = $width;
    }

    /**
     * Returns the height of the image.
     *
     * @api
     *
     * @return integer the height of the image
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * Returns the width of the image.
     *
     * @api
     *
     * @return integer the width of the image
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Parses a proper bit of unmarshaled JSON into an ImageLink object.
     * This is used internally during the unmarshaling of API calls.
     *
     * @param \stdClass $json the raw JSON that needs to be transformed into native objects.
     *
     * @return ImageLink the new object that was created form the JSON.
     */
    public static function parse($json)
    {
        return new ImageLink(
            $json->image->url,
            $json->image->kind,
            $json->image->size,
            $json->image->name,
            $json->image->height,
            $json->image->width
        );
    }
}
