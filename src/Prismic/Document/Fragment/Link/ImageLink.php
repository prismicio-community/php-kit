<?php
declare(strict_types=1);

namespace Prismic\Document\Fragment\Link;

use Prismic\Document\Fragment\FragmentInterface;
use Prismic\LinkResolver;

class ImageLink extends FileLink
{

    protected $height;

    protected $width;

    public static function factory($value, LinkResolver $linkResolver) : FragmentInterface
    {
        /** @var ImageLink $link */
        $link = parent::factory($value, $linkResolver);

        $value = isset($value->value) ? $value->value : $value;
        $value = isset($value->image) ? $value->image : $value;

        $link->height = isset($value->height) ? $value->height : null;
        $link->width  = isset($value->width) ? $value->width : null;

        return $link;
    }
}
