<?php
declare(strict_types=1);

namespace Prismic\Document\Fragment\Link;

use Prismic\Document\Fragment\HtmlHelperTrait;
use Prismic\Document\Fragment\LinkInterface;
use Prismic\Exception\InvalidArgumentException;
use Prismic\Json;
use Prismic\LinkResolver;
use function count;
use function sprintf;
use function substr;

abstract class AbstractLink implements LinkInterface
{
    use HtmlHelperTrait;

    /** @var string|null */
    protected $target;

    public static function abstractFactory(object $value, LinkResolver $linkResolver) :? LinkInterface
    {
        // Inspect payload to determine link type
        $linkType = $value->link_type ?? null;
        $linkType = isset($value->value, $value->type) ? $value->type : $linkType;

        /**
         * In V2, link fields that have not been assigned a target are non-empty objects, hooray!
         * Rather than throw an exception, return null
         */
        if (isset($value->link_type) && count((array) $value) === 1) {
            return null;
        }

        if ($linkType === null) {
            throw new InvalidArgumentException(sprintf(
                'Expected a payload describing a link, received %s',
                Json::encode($value)
            ));
        }

        // In V2, you have to look at $value->kind in order to figure out if it's an image or a file
        if ($linkType === 'Media') {
            $subType = null;
            if (isset($value->kind) && $value->kind === 'image') {
                $subType = 'Link.image';
            }

            if (isset($value->kind) && $value->kind === 'document') {
                $subType = 'Link.file';
            }

            if (! $subType) {
                throw new InvalidArgumentException(sprintf(
                    'Encountered a V2 Media link but the subtype was neither image, nor document. Got %s',
                    (string) $value->kind
                ));
            }

            $linkType = $subType;
        }

        $link = null;
        switch ($linkType) {
            case 'Link.document':
            case 'Document':
                $link = DocumentLink::linkFactory($value, $linkResolver);
                break;
            case 'Link.web':
            case 'Web':
                $link = WebLink::linkFactory($value, $linkResolver);
                break;
            case 'Link.image':
                $link = ImageLink::linkFactory($value, $linkResolver);
                break;
            case 'Link.file':
                $link = FileLink::linkFactory($value, $linkResolver);
                break;
        }

        if ($link === null) {
            throw new InvalidArgumentException(sprintf(
                'Cannot determine a link from the given payload: %s',
                Json::encode($value)
            ));
        }

        return $link;
    }

    protected function __construct()
    {
    }

    public function getId() :? string
    {
        return null;
    }

    public function getUid() :? string
    {
        return null;
    }

    public function getType() :? string
    {
        return null;
    }

    /** @return string[] */
    public function getTags() :? array
    {
        return [];
    }

    public function getSlug() :? string
    {
        return null;
    }

    public function getLang() :? string
    {
        return null;
    }

    public function getTarget() : ?string
    {
        return $this->target;
    }

    public function isBroken() : bool
    {
        return false;
    }

    public function __toString() : string
    {
        $url = $this->getUrl();

        return $url ?: '';
    }

    public function asText() : ?string
    {
        return $this->getUrl();
    }

    public function openTag() : ?string
    {
        $url = $this->getUrl();
        if (! $url) {
            return null;
        }

        $attributes = [
            'href' => $this->getUrl(),
        ];
        if ($this->getTarget()) {
            $attributes['target'] = $this->getTarget();
            $attributes['rel'] = 'noopener';
        }

        if ($this->getLang()) {
            $attributes['hreflang'] = substr($this->getLang(), 0, 2);
        }

        return sprintf(
            '<a%s>',
            $this->htmlAttributes($attributes)
        );
    }

    public function closeTag() :? string
    {
        $url = $this->getUrl();
        if (! $url) {
            return null;
        }

        return '</a>';
    }

    public function asHtml() : ?string
    {
        $url = $this->getUrl();
        if (! $url) {
            return null;
        }

        return sprintf(
            '%s%s%s',
            $this->openTag(),
            $this->escapeHtml($url),
            $this->closeTag()
        );
    }
}
