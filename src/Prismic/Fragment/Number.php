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
 * This class embodies a Number fragment.
 */
class Number implements FragmentInterface
{
    /**
     * @var integer the integer value of the number
     */
    private $value;

    /**
     * Constructs a Number fragment.
     *
     * @param integer $value the integer value of the number
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * Formats the number along a given pattern
     *
     * @api
     *
     * @param string $pattern the pattern, as would be expected by sprintf
     */
    public function format($pattern)
    {
        return sprintf($pattern, $this->value);
    }

    /**
     * Builds a text version of the Number fragment (simply returns its value)
     *
     * @api
     *
     * @return integer the text version of the Number fragment
     */
    public function asText()
    {
        return $this->getValue();
    }

    /**
     * Builds a HTML version of the Number fragment
     *
     * @api
     *
     * @param \Prismic\LinkResolver $linkResolver the link resolver
     *
     * @return string the HTML version of the Number fragment
     */
    public function asHtml($linkResolver = null)
    {
        return '<span class="number">' . $this->value . '</span>';
    }

    /**
     * Returns the number's value.
     *
     * @api
     *
     * @return integer the number's value
     */
    public function getValue()
    {
        return $this->value;
    }
}
