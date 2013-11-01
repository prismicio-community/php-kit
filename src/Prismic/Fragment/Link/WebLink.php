<?php

/*
 * This file is part of the Prismic PHP SDK
 *
 * Copyright 2013 Zengularity (http://www.zengularity.com).
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Prismic\Fragment\Link;

class WebLink implements LinkInterface
{
    private $url;
    private $maybeContentType;

    public function __construct($url, $maybeContentType = null)
    {
        $this->url = $url;
        $this->maybeContentType = $maybeContentType;
    }

    public function asHtml($linkResolver = null)
    {
        return '<a href="' . $this->url . '">$url</a>';
    }

    public function asText()
    {
        return $this->getUrl();
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getContentType()
    {
        return $this->maybeContentType;
    }

    public static function parse($json)
    {
        return new WebLink($json->url);
    }
}
