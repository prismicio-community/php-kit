<?php

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
     * Returns the application-specific URL related to this document link
     *
     * @param Json $doc The document link
     *
     * @return string
     */
    abstract public function resolve($doc);

    /**
     * What happens when the link resolver gets called.
     *
     * @param Json $doc The document link

     * @return string
     */
    public function __invoke($doc)
    {
        return $this->resolve($doc);
    }
}
