<?php
declare(strict_types=1);

namespace Prismic\Document\Fragment\Link;

use Prismic\Document\Fragment\LinkInterface;
use Prismic\Exception\InvalidArgumentException;
use Prismic\Json;
use Prismic\LinkResolver;
use function sprintf;

class WebLink extends AbstractLink
{
    /** @var string */
    protected $url;

    public function getUrl() :? string
    {
        return $this->url;
    }

    public static function linkFactory(object $value, LinkResolver $linkResolver) : LinkInterface
    {
        $link = new static();
        $value = $value->value ?? $value;
        $value = $value->image ?? $value;
        $value = $value->file ?? $value;

        if (! isset($value->url)) {
            throw new InvalidArgumentException(sprintf(
                'Expected value to contain a url property, received %s',
                Json::encode($value)
            ));
        }

        $link->url    = $value->url;
        $link->target = $value->target ?? null;

        return $link;
    }
}
