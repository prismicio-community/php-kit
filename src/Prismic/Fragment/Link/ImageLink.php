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

class ImageLink extends FileLink
{
    private $height;
    private $width;

    public function __construct($url, $kind, $size, $filename, $height, $width)
    {
        parent::__construct($url, $kind, $size, $filename);
        $this->height = $height;
        $this->width = $width;
    }

    public function getHeight()
    {
        return $this->height;
    }

    public function getWidth()
    {
        return $this->width;
    }

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
