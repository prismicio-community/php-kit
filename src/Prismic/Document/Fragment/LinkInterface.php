<?php
declare(strict_types=1);

namespace Prismic\Document\Fragment;

use Prismic\LinkResolver;

interface LinkInterface extends FragmentInterface
{
    public static function linkFactory(object $value, LinkResolver $linkResolver) : LinkInterface;

    public function getUrl() :? string;

    public function getId() :? string;

    public function getUid() :? string;

    public function getType() :? string;

    /** @return string[] */
    public function getTags() :? array;

    public function getSlug() :? string;

    public function getLang() :? string;

    public function getTarget() :? string;

    public function isBroken() : bool;

    public function __toString() : string;

    public function openTag() : ?string;

    public function closeTag() :? string;
}
