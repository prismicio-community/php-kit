<?php
declare(strict_types=1);

namespace My;

use Prismic\LinkResolverAbstract;
use Prismic\Document\Fragment\Link\DocumentLink;

class LinkResolverExample extends LinkResolverAbstract
{

    protected function resolveDocumentLink(DocumentLink $link) :? string
    {
        /**
         * The document link provided has the following methods to help construct your
         * application specific URL
         *
         * $link->getId()   - Universally Unique Document Identifier, i.e. Wxy8hRtk_r
         * $link->getUid()  - Unique to each TYPE of document, i.e. 'about-us'.
         *                  - The UID is guaranteed to exist if your document type has a specific UID field
         * $link->getType() - The type of document, i.e. 'web-page'
         * $link->getSlug() - Computed by prismic, based normally on a heading somewhere, i.e. 'the-document-title'
         * $link->getTags() - An array of tags where each element is a string
         * $link->getLang() - The language of the document
         */

        return sprintf('/app/%s/%s', $link->getType(), $link->getUid());
    }
}
