<?php
declare(strict_types=1);

namespace Prismic\Document\Fragment;

use Prismic\LinkResolver;

interface FragmentInterface
{

    public static function factory($value, LinkResolver $linkResolver) : FragmentInterface;

    public function asText() :? string;

    public function asHtml() :? string;
}
