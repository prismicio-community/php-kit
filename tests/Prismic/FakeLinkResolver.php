<?php

namespace Prismic\Test;

use Prismic\LinkResolver;

class FakeLinkResolver extends LinkResolver
{
    public function resolve($link)
    {
        return "http://host/doc/".$link->getId();
    }
}
