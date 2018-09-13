<?php
declare(strict_types=1);

namespace Prismic;

/**
 * The LinkResolver convert Prismic's links into your application's ones.
 * Since Prismic can't possibly know how to resolve internal links into usable
 * ones for your application, you have to teach it! That way, you can pass this knowledge
 * as a LinkResolver object into any operation that may have to resolve URLs
 * (such as calling asHtml() on RichText, or asUrl() on Link).
 *
 * If you're using a starter project, it usually comes with a basic linkResolver
 * object, located at a relevant place, and made available everywhere in your application.
 *
 * Read the link resolving page on
 * <a href="https://prismic.io/docs/php/beyond-the-api/link-resolving">Prismic's API documentation</a>
 * to better understand the idea.
 */
abstract class LinkResolver
{
    /**
     * Returns the application-specific URL related to this document link
     *
     *
     * @param object $link The document link
     *
     * @return string|null The URL of the link
     */
    abstract public function resolve($link) :? string;

    /**
     * What happens when the link resolver gets called.
     *
     *
     * @param object $link The document link
     *
     * @return string|null The URL of the link
     */
    public function __invoke($link) :? string
    {
        return $this->resolve($link);
    }
}
