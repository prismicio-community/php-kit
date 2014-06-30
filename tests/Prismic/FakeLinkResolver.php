<?php

namespace Prismic\Test;

use Prismic\LinkResolver;

class FakeLinkResolver extends LinkResolver
{
    public function resolve($link)
    {
        if ($link->isBroken()) {
            return null;
        }
        return "http://host/doc/".$link->getId();
    }
}
