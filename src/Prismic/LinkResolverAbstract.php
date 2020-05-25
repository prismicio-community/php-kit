<?php
declare(strict_types=1);

namespace Prismic;

use Prismic\Document\Fragment\Link\DocumentLink;
use Prismic\Document\Fragment\LinkInterface;

abstract class LinkResolverAbstract implements LinkResolver
{
    public function resolve(LinkInterface $link) :? string
    {
        if ($link instanceof DocumentLink) {
            if (! $link->isBroken()) {
                return $this->resolveDocumentLink($link);
            }

            return null;
        }

        return $link->getUrl();
    }

    public function __invoke(LinkInterface $link) :?string
    {
        return $this->resolve($link);
    }

    abstract protected function resolveDocumentLink(DocumentLink $link) :? string;
}
