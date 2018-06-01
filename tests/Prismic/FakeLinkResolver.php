<?php
declare(strict_types=1);

namespace Prismic\Test;

use Prismic\Document\Fragment\Link\DocumentLink;
use Prismic\LinkResolverAbstract;

class FakeLinkResolver extends LinkResolverAbstract
{
    protected function resolveDocumentLink(DocumentLink $link) :? string
    {
        return 'RESOLVED_LINK';
    }
}
