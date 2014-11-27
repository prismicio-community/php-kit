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
 * This class embodies a Timestamp fragment.
 */
class Timestamp implements FragmentInterface
{
    /**
     * @var string the timestamp's value
     */
    private $value;

    /**
     * Constructs a Timestamp fragment.
     *
     * @param string  $value  the timestamp's value
     */
    public function __construct($value)
    {
        $this->value = $value;
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
        return DateTime::createFromFormat(DateTime::ISO8601, $this->value);
    }

    /**
     * Builds a HTML version of the timestamp.
     *
     * @api
     *
     * @param \Prismic\LinkResolver $linkResolver the link resolver
     *
     * @return string the HTML version of the timestamp
     */
    public function asHtml($linkResolver = null)
    {
        $datetime = $this->asDateTime()->format('c');

        return sprintf('<time datetime="%s">%s</time>',
            $datetime,
            htmlentities($this->value)
        );
    }

    /**
     * Builds a text version of the timestamp.
     *
     * @api
     *
     * @return string the text version of the timestamp
     */
    public function asText()
    {
        return $this->value;
    }

    /**
     * Returns the timestamp's value.
     *
     * @api
     *
     * @return string the timestamp's value
     */
    public function getValue()
    {
        return $this->value;
    }
}
