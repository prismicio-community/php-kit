<?php
declare(strict_types=1);

namespace Prismic\Document\Fragment\Link;

use Prismic\Document\Fragment\LinkInterface;
use Prismic\LinkResolver;
use function assert;

class ImageLink extends FileLink
{
    /** @var int|null */
    protected $height;

    /** @var int|null */
    protected $width;

    public static function linkFactory(object $value, LinkResolver $linkResolver) : LinkInterface
    {
        $link = parent::linkFactory($value, $linkResolver);
        assert($link instanceof self);

        $value = $value->value ?? $value;
        $value = $value->image ?? $value;

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
