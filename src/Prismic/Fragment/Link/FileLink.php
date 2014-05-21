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

class FileLink implements LinkInterface
{
    protected $url;
    protected $kind;
    protected $size;
    protected $filename;

    public function __construct($url, $kind, $size, $filename)
    {
        $this->url = $url;
        $this->kind = $kind;
        $this->size = $size;
        $this->filename = $filename;
    }

    public function asHtml($linkResolver = null)
    {
        return '<a href="' . $this->url . '">' . $this->filename . '</a>';
    }

    public function asText()
    {
        return $this->getUrl();
    }

    public function getUrl($linkResolver = null)
    {
        return $this->url;
    }

    public function getKind()
    {
        return $this->kind;
    }

    public function getSize()
    {
        return $this->size;
    }

    public function getFilename()
    {
        return $this->filename;
    }

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
