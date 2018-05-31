<?php
declare(strict_types=1);

namespace Prismic\Document\Fragment\Link;

use Prismic\Document\Fragment\LinkInterface;
use Prismic\LinkResolver;

class ImageLink extends FileLink
{

    protected $height;

    protected $width;

    public static function linkFactory($value, LinkResolver $linkResolver) : LinkInterface
    {
        /** @var ImageLink $link */
        $link = parent::linkFactory($value, $linkResolver);

        $value = isset($value->value) ? $value->value : $value;
        $value = isset($value->image) ? $value->image : $value;

        $link->height = isset($value->height) ? (int) $value->height : null;
        $link->width  = isset($value->width) ? (int) $value->width : null;

        return $link;
    }

    public function getWidth() :? int
    {
        return $this->width;
    }

    public function getHeight() :? int
    {
        return $this->height;
    }
}
