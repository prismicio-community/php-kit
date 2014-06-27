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
 * This interface embodies any fragment ; all fragment classes implement it.
 */
interface FragmentInterface
{
    /**
     * Return the value of the fragment as text.
     *
     * @api
     *
     * @return string
     */
    public function asText();

    /**
     * Return the value of the fagment as HTML.
     *
     * @api
     *
     * @param \Prismic\LinkResolver $linkResolver the link resolver
     *
     * @return string
     */
    public function asHtml($linkResolver);
}
