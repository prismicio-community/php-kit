<?php
declare(strict_types=1);

namespace Prismic\Document\Fragment\Link;

use Prismic\Document\Fragment\FragmentInterface;
use Prismic\Exception\InvalidArgumentException;
use Prismic\LinkResolver;

class WebLink extends AbstractLink
{

    /** @var string */
    protected $url;


    public function getUrl() : ?string
    {
        return $this->url;
    }

    public static function factory($value, LinkResolver $linkResolver) : FragmentInterface
    {
        /** @var WebLink $link */
        $link = new static();
        $value = isset($value->value) ? $value->value : $value;
        $value = isset($value->image) ? $value->image : $value;
        $value = isset($value->file) ? $value->file : $value;

        if (! isset($value->url)) {
            throw new InvalidArgumentException(\sprintf(
                'Expected value to contain a url property, received %s',
                \json_encode($value)
            ));
        }

        $link->url    = $value->url;
        $link->target = isset($value->target) ? $value->target : null;
        return $link;
    }
}
