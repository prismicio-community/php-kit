<?php
/**
 * This file is part of the Prismic PHP SDK
 *
 * Copyright 2013 Zengularity (http://www.zengularity.com).
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Prismic;

/**
 * The LinkResolver convert prismic.io's links into your application's ones.
 * Since prismic.io can't possibly know how to resolve internal links into usable
 * ones for your application, you have to teach it! That way, you can pass this knowledge
 * as a LinkResolver object into any operation that may have to resolve URLs
 * (such as calling asHtml() on StructuredText or Link fragments).
 *
 * If you're using a starter project, it usually comes with a basic linkResolver
 * object, located at a relevant place, and made available everywhere in your application.
 *
 * Read the last paragraph of
 * <a href="https://developers.prismic.io/documentation/UjBe8bGIJ3EKtgBZ/api-documentation">prismic.io's API documentation</a>
 * to better understand the idea.
 */
abstract class LinkResolver
{

    /**
     * Returns the application-specific URL related to this document link, or
     * null if the link is deemed invalid
     *
     * @param Fragment\Link\DocumentLink $link The document link
     *
     * @return String or null
     */
    abstract public function resolve($link);

    /**
     * What happens when the link resolver gets called.
     *
     * @param Fragment\Link\DocumentLink $link The document link
     * @return String
     */
    public function __invoke($link)
    {
        return $this->resolve($link);
    }

    /**
     * Returns the application-specific URL related to this Document
     *
     * @param Document $document The document
     *
     * @return String
     */
    public function resolveDocument($document)
    {
        return $this->resolve($this->asLink($document));
    }

    /**
     * Returns the application-specific URL related to this document link
     *
     * @param Fragment\Link\DocumentLink $link The document link
     *
     * @return String
     */
    public function resolveLink($link)
    {
        return $this->resolve($link);
    }

    /**
     * Returns true if the given document corresponds to the given bookmark
     *
     * @param API      $api      The API
     * @param Document $document The document to test
     * @param String   $bookmark The bookmark to test
     *
     * @return true if the given document corresponds to the given bookmark
     */
    public function isBookmarkDocument($api, $document, $bookmark)
    {
        return $this->isBookmark($api, $this->asLink($document), $bookmark);
    }

    /**
     * Returns true if the given document link corresponds to the given bookmark
     *
     * @param API                        $api      The API
     * @param Fragment\Link\DocumentLink $link     The document link to test
     * @param String                     $bookmark The bookmark to test
     *
     * @return true if the given document corresponds to the given bookmark
     */
    public function isBookmark($api, $link, $bookmark)
    {
        $maybeId = $api->bookmark($bookmark);
        if ($maybeId == $link->getId()) {
            return true;
        }

        return false;
    }

    /**
     * This method convert a document into document link
     *
     * @param Document $document The document
     *
     * @return Fragment\Link\DocumentLink The document link
     */
    private function asLink($document)
    {
        return new Fragment\Link\DocumentLink(
            $document->getId(),
            $document->getUid(),
            $document->getType(),
            $document->getTags(),
            $document->getSlug(),
            $document->getFragments(),
            false
        );
    }
}
