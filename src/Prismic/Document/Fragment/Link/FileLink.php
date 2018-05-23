<?php
declare(strict_types=1);

namespace Prismic\Document\Fragment\Link;

use Prismic\Document\Fragment\FragmentInterface;
use Prismic\Document\Fragment\HtmlHelperTrait;
use Prismic\LinkResolver;

class FileLink extends WebLink
{

    use HtmlHelperTrait;

    protected $filename;

    protected $filesize;

    public static function factory($value, LinkResolver $linkResolver) : FragmentInterface
    {
        /** @var FileLink $link */
        $link = parent::factory($value, $linkResolver);
        // V1
        $value = isset($value->value) ? $value->value : $value;
        $value = isset($value->file) ? $value->file : $value;

        $link->filename = isset($value->name) ? $value->name : null;
        $link->filesize = isset($value->size) ? $value->size : null;

        return $link;
    }

    public function getFilesize() :? int
    {
        return $this->filesize;
    }

    public function getFilename() :? string
    {
        return $this->filename;
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
            $this->escapeHtml($this->getFilename()),
            $this->closeTag()
        );
    }
}
