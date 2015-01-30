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
 * This class embodies a Text fragment.
 */
class Text implements FragmentInterface
{
    /**
     * @var string  the text value of the fragment
     */
    private $value;

    /**
     * Constructs a Text object.
     *
     * @param string    $value   the text value of the fragment
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * Builds a HTML version of the Text fragment.
     *
     * @api
     *
     * @param \Prismic\LinkResolver $linkResolver the link resolver
     *
     * @return string the HTML version of the Text fragment
     */
    public function asHtml($linkResolver = null)
    {
        return '<span class="text">' . nl2br(htmlentities($this->value, null, 'UTF-8')) . '</span>';
    }

    /**
     * Builds a text version of the Text fragment.
     *
     * @api
     *
     * @return string the text version of the Text fragment
     */
    public function asText()
    {
        return $this->getValue();
    }

    /**
     * Returns the text value of the fragment.
     *
     * @api
     *
     * @return  string  the text value of the fragment
     */
    public function getValue()
    {
        return $this->value;
    }
}
