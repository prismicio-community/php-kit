<?php

namespace Prismic\Test;

use Prismic\Fragment\Link\DocumentLink;
use Prismic\LinkResolver;

class FakeLinkResolver extends LinkResolver
{
    /**
     * @param DocumentLink $link
     *
     * @return null|string
     */
    public function resolve($link)
    {
        if ($link->isBroken()) {
            return null;
        }
        return "http://host/doc/".$link->getId();
    }
}
