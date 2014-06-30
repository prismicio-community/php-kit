<?php
/**
 * This file is part of the Prismic PHP SDK
 *
 * Copyright 2013 Zengularity (http://www.zengularity.com).
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Prismic\Fragment;

use DOMDocument;

/**
 * This class embodies an image view.
 * An image in prismic.io is made of views: a main unnamed one, and optional named ones.
 * Typically, views are different sizes of the same image ("icon", "large", ...),
 * but not necessarily.
 */
class ImageView
{
    /**
     * @var string  the image view's URL
     */
    private $url;
    /**
     * @var string  the image view's alternative text
     */
    private $alt;
    /**
     * @var string  the image view's copyright
     */
    private $copyright;
    /**
     * @var integer the image view's width
     */
    private $width;
    /**
     * @var integer the image view's height
     */
    private $height;
    /**
     * @var \Prismic\Fragment\Link\LinkInterface the link to point to, or null
     */
    private $link;

    /**
     * Constructs an image view.
     *
     * @param string  $url          the image view's URL
     * @param string  $alt          the image view's alternative text
     * @param string  $copyright    the image view's copyright
     * @param string  $width        the image view's width
     * @param string  $height       the image view's height
     * @param string  $link         the image view's link to point to
     */
    public function __construct($url, $alt, $copyright, $width, $height, $link)
    {
        $this->url = $url;
        $this->alt = $alt;
        $this->copyright = $copyright;
        $this->width = $width;
        $this->height = $height;
        $this->link = $link;
    }

    /**
     * Builds a HTML version of the image view.
     *
     * @api
     *
     * @param \Prismic\LinkResolver $linkResolver the link resolver
     * @param array                 $attributes   associative array of HTML attributes to add to the <img> tag
     *
     * @return string the HTML version of the image view
     */
    public function asHtml($linkResolver = null, $attributes = array())
    {
        $doc = new DOMDocument();
        $img = $doc->createElement('img');
        $attributes = array_merge(array(
            'src' => $this->getUrl(),
            'alt' => htmlentities($this->getAlt()),
            'width' => $this->getWidth(),
            'height' => $this->getHeight(),
        ), $attributes);
        foreach ($attributes as $key => $value) {
            $img->setAttribute($key, $value);
        }

        if ($this->getLink() && ($url = $this->getLink()->getUrl($linkResolver)) !== null) {
            $a = $doc->createElement('a');
            $a->setAttribute('href', $url);
            $a->appendChild($img);
            $doc->appendChild($a);
        } else {
            $doc->appendChild($img);
        }

        return trim($doc->saveHTML()); // trim removes trailing newline
    }

    /**
     * Returns the ratio of the image view.
     *
     * @api
     *
     * @return integer  the image view's ratio
     */
    public function ratio()
    {
        return $this->width / $this->height;
    }

    /**
     * Returns the URL of the image view.
     *
     * @api
     *
     * @return string  the image view's URL
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Returns the alternative text of the image view.
     *
     * @api
     *
     * @return string  the image view's alternative text
     */
    public function getAlt()
    {
        return $this->alt;
    }

    /**
     * Returns the copyright text of the image view.
     *
     * @api
     *
     * @return string  the image view's copyright text
     */
    public function getCopyright()
    {
        return $this->copyright;
    }

    /**
     * Returns the width of the image view.
     *
     * @api
     *
     * @return integer  the image view's width
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Returns the height of the image view.
     *
     * @api
     *
     * @return integer  the image view's height
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * Returns the link to point to
     *
     * @api
     *
     * @return \Prismic\Fragment\Link\LinkInterface the link to point to
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * Parses a given image view fragment. Not meant to be used except for testing.
     *
     * @param  \stdClass                    $json the json bit retrieved from the API that represents an image view.
     * @return \Prismic\Fragment\ImageView  the manipulable object for that image view.
     */
    public static function parse($json)
    {
        return new ImageView(
            $json->url,
            $json->alt,
            $json->copyright,
            $json->dimensions->width,
            $json->dimensions->height,
            isset($json->linkTo) ? StructuredText::extractLink($json->linkTo) : null
        );
    }
}
