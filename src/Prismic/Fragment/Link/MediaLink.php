<?php

namespace Prismic\Fragment\Link;

class MediaLink implements LinkInterface
{
    private $url;
    private $kind;
    private $size;
    private $filename;

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

    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }

    public static function parse($json)
    {
        return new MediaLink(
            $json->file->url,
            $json->file->kind,
            $json->file->size,
            $json->file->name
        );
    }
}