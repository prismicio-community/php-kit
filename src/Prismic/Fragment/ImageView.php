<?php

/*
 * This file is part of the Prismic PHP SDK
 *
 * Copyright 2013 Zengularity (http://www.zengularity.com).
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Prismic\Fragment;

use DOMDocument;

class ImageView
{
    private $url;
    private $alt;
    private $copyright;
    private $width;
    private $height;
    /**
     * @var \Prismic\Fragment\Link\LinkInterface the link to point to, or null
     */
    private $link;

    public function __construct($url, $alt, $copyright, $width, $height, $link)
    {
        $this->url = $url;
        $this->alt = $alt;
        $this->copyright = $copyright;
        $this->width = $width;
        $this->height = $height;
        $this->link = $link;
    }

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

        if ($this->getLink()) {
            $a = $doc->createElement('a');
            $a->setAttribute('href', $this->getLink()->getUrl($linkResolver));
            $a->appendChild($img);
            $doc->appendChild($a);
        } else {
            $doc->appendChild($img);
        }

        return trim($doc->saveHTML()); // trim removes trailing newline
    }

    public function ratio()
    {
        return $this->width / $this->height;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getAlt()
    {
        return $this->alt;
    }

    public function getCopyright()
    {
        return $this->copyright;
    }

    public function getWidth()
    {
        return $this->width;
    }

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
