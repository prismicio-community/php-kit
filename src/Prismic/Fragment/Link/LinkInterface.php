<?php
/**
 * This file is part of the Prismic PHP SDK
 *
 * Copyright 2013 Zengularity (http://www.zengularity.com).
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Prismic\Fragment\Link;

use Prismic\Fragment\FragmentInterface;

/**
 * This interface embodies any link.
 * LinkInterface objects can be found in two occasions: as the "$link" variable of a HyperlinkSpan object
 * (which happens when the link is a hyperlink in a StructuredText fragment), or the LinkInterface
 * can also be its own fragment (e.g. for a "related" fragment, that links to a related document).
 *
 * Its known implementations are DocumentLink, WebLink, and also FileLink and ImageLink
 * (united as "MediaLink" in former versions of the kit).
 */
interface LinkInterface extends FragmentInterface
{
    /**
     * Returns the URL we're linking to.
     * The linkResolver will be needed in case the link is a document link.
     * Read more about the link resolver at the very end of prismic.io's documentation.
     *
     * @api
     *
     * @param \Prismic\LinkResolver $linkResolver the link resolver (read prismic.io's API documentation to learn more)
     *
     * @return string the URL of the resource we're linking to online
     */
    public function getUrl($linkResolver = null);
}
