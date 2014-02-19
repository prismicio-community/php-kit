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

class Image implements FragmentInterface
{
    private $main;
    private $views;

    public function __construct($main, $views = array())
    {
        $this->main = $main;
        $this->views = $views;
    }

    public function asHtml($linkResolver = null)
    {
        return $this->main->asHtml();
    }

    public function asText()
    {
        return $this->main->getUrl();
    }

    public function getView($key)
    {
        if (strtolower($key) == "main") {
            return $this->main;
        }

        return $this->views[$key];
    }

    public function getMain()
    {
        return $this->main;
    }

    public function getViews()
    {
        return $this->views;
    }
}
