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

    public function __construct($url, $alt, $copyright, $width, $height)
    {
        $this->url = $url;
        $this->alt = $alt;
        $this->copyright = $copyright;
        $this->width = $width;
        $this->height = $height;
    }

    public function asHtml()
    {
        $doc = new DOMDocument();
        $img = $doc->createElement('img');
        $img->setAttribute('src', $this->getUrl());
        $img->setAttribute('width', $this->getWidth());
        $img->setAttribute('height', $this->getHeight());
        $doc->appendChild($img);
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

    public static function parse($json)
    {
        return new ImageView(
            $json->url,
            $json->alt,
            $json->copyright,
            $json->dimensions->width,
            $json->dimensions->height
        );
    }
}
