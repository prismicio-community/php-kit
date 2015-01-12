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
use DateTime;

/**
 * This class embodies a date fragment.
 */
class Date implements FragmentInterface
{
    /**
     * @var string the date's value
     */
    private $value;

    /**
     * Constructs a Date fragment.
     *
     * @param string  $value  the date's value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * Builds a HTML version of the date.
     *
     * @api
     *
     * @param \Prismic\LinkResolver $linkResolver the link resolver
     *
     * @return string the HTML version of the date
     */
    public function asHtml($linkResolver = null)
    {
        return '<time>' . htmlentities($this->value) . '</time>';
    }

    /**
     * Builds a text version of the date.
     *
     * @api
     *
     * @return string the text version of the date
     */
    public function asText()
    {
        return $this->value;
    }

    /**
     * Builds a DateTime from the Timestamp fragment, for further manipulation in native PHP.
     *
     * @api
     *
     * @return string a DateTime for the fragment
     */
    public function asDateTime()
    {
        return DateTime::createFromFormat('Y-m-d', $this->value)->setTime(0, 0, 0);
    }

    /**
     * Returns the date following a certain pattern.
     *
     * @param string  $pattern  the pattern, as expected by the date function in PHP
     *
     * @api
     *
     * @return string the date, formatted
     */
    public function formatted($pattern)
    {
        return date($pattern, $this->asEpoch());
    }

    /**
     * Returns the date as an epoch date.
     *
     * @api
     *
     * @return string the date as an epoch date
     */
    public function asEpoch()
    {
        return strtotime($this->value);
    }

    /**
     * Returns the date's value.
     *
     * @api
     *
     * @return string the date's value
     */
    public function getValue()
    {
        return $this->value;
    }
}
