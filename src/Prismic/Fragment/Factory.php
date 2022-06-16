<?php

namespace Prismic\Fragment;

class Factory
{
    public const
        DEFAULT_RENDERER    = Block::class,
        RENDERERS = [
            'image'         => Image::class,
            'embed'         => Embed::class,
            'hyperlink'     => Hyperlink::class,
        ];

    public static function create(\stdClass $element): BlockInterface
    {
        /** @var BlockInterface $class */
        $class = self::RENDERERS[$element->type ?? ''] ?? self::DEFAULT_RENDERER;
        return new $class($element);
    }
}
