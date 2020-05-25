<?php
declare(strict_types=1);

namespace Prismic\Document\Fragment;

interface FragmentInterface
{
    public function asText() :? string;

    public function asHtml() :? string;
}
