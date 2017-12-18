<?php

namespace Prismic\Test;

use Prismic\LinkResolver;

class FakeLinkResolver extends LinkResolver
{
    public function resolve($link)
    {
        if ($link->isBroken) {
            return 'http://host/404';
        }

        return 'http://host/doc/'.$link->id;
    }
}
