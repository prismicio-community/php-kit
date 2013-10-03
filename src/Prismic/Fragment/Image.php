<?php

namespace Prismic\Fragment;

class Image implements FragmentInterface
{
    private $main;
    private $views;

    public function __construct($main, $views)
    {
        $this->main = $main;
        $this->views = $views;
    }

    public function asHtml()
    {
        return $this->main->asHtml();
    }

    public function getView($key)
    {
        if (strtolower($key) == "main") {
            return $this->main;
        }
        else {
            return $this->views[$key];
        }
    }
}
