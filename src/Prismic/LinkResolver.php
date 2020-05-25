<?php
declare(strict_types=1);

namespace Prismic;

use Prismic\Document\Fragment\LinkInterface;

interface LinkResolver
{
    public function resolve(LinkInterface $link) :? string;

    public function __invoke(LinkInterface $link) :? string;
}
