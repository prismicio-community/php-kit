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
 * This class embodies a Color fragment.
 *
 * @api
 */
class Color implements FragmentInterface
{
    /**
     * @var string the hexadecimal code of the color
     */
    private $hex;

    /**
     * Constructs a Color fragment.
     *
     * @param string  $hex  the hexadecimal code of the color
     */
    public function __construct($hex)
    {
        $this->hex = $hex;
    }

    /**
     * Builds a HTML version of the color.
     *
     * @api
     *
     * @param \Prismic\LinkResolver $linkResolver the link resolver
     *
     * @return string the HTML version of the color
     */
    public function asHtml($linkResolver = null)
    {
        return '<span class="color">' . htmlentities($this->hex) . '</span>';
    }

    /**
     * Builds a text version of the color.
     *
     * @api
     *
     * @return string the text version of the color
     */
    public function asText()
    {
        return $this->getHexValue();
    }

    /**
     * Returns the hexadecimal code of the color.
     *
     * @api
     *
     * @return string the hexadecimal code of the color
     */
    public function getHexValue()
    {
        return $this->hex;
    }
}
