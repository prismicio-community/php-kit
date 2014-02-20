<?php

/*
 * This file is part of the Prismic PHP SDK
 *
 * Copyright 2013 Zengularity (http://www.zengularity.com).
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Prismic;

/**
 * The LinkResolver convert Prismic.io's links into Application's ones.
 */
abstract class LinkResolver
{

    /**
     * Returns the application-specific URL related to this document link
     *
     * @param Fragment\Link\DocumentLink $link The document link
     *
     * @return String
     */
    abstract public function resolve($link);

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
            $document->getType(),
            $document->getTags(),
            $document->getSlug(),
            false
        );
    }
}
