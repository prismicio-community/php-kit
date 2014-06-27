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

/**
 * This class embodies an Image fragment.
 * An image in prismic.io is made of views: a main unnamed one, and optional named ones.
 * Typically, views are different sizes of the same image ("icon", "large", ...),
 * but not necessarily.
 */
class Image implements FragmentInterface
{
    /**
     * @var \Prismic\Fragment\ImageView  the main view of the image
     */
    private $main;
    /**
     * @var array  the associative array of \Prismic\Fragment\ImageView objects
     */
    private $views;

    /**
     * Constructs an image fragment.
     *
     * @param \Prismic\Fragment\ImageView  $main    the main view of the image
     * @param array                        $views   the associative array of \Prismic\Fragment\ImageView objects
     */
    public function __construct($main, $views = array())
    {
        $this->main = $main;
        $this->views = $views;
    }

    /**
     * Builds a HTML version of the Image fragment (using just the main view)
     *
     * @api
     *
     * @param \Prismic\LinkResolver $linkResolver the link resolver
     *
     * @return string the HTML version of the Image fragment
     */
    public function asHtml($linkResolver = null)
    {
        return $this->main->asHtml();
    }

    /**
     * Builds a text version of the Image fragment (simply returns its URL)
     *
     * @api
     *
     * @return string the text version of the Image fragment
     */
    public function asText()
    {
        return $this->main->getUrl();
    }

    /**
     * Returns an image view from its name (for instance, "icon", "large", ...)
     *
     * @api
     *
     * @param string $key the name of the view
     *
     * @return \Prismic\Fragment\ImageView the image view
     */
    public function getView($key)
    {
        if (strtolower($key) == "main") {
            return $this->main;
        }

        return $this->views[$key];
    }

    /**
     * Returns the main image view
     *
     * @api
     *
     * @return \Prismic\Fragment\ImageView the main image view
     */
    public function getMain()
    {
        return $this->main;
    }

    /**
     * Returns an associative array of all the views for this image but the main one.
     *
     * @api
     *
     * @return array the array of all the \Prismic\Fragment\ImageView objects but the main one
     */
    public function getViews()
    {
        return $this->views;
    }
}
