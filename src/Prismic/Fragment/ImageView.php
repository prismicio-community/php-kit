<?php

namespace Prismic\Fragment;

class ImageView
{

    private $url;
    private $width;
    private $height;

    public function __construct($url, $width, $height)
    {
        $this->url = $url;
        $this->width = $width;
        $this->height = $height;
    }

    public function asHtml()
    {
        return '<img src="' . $this->url . '" width="' . $this->width . '" height="' . $this->height . '"/>';
    }

    public function ratio()
    {
        return $this->width / $this->height;
    }

    public static function parse($json)
    {
        return new ImageView(
            $json->url,
            $json->dimensions->width,
            $json->dimensions->height
        );
    }
}