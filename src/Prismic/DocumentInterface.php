<?php
declare(strict_types=1);

namespace Prismic;

use DateTimeInterface;
use Prismic\Document\Fragment\FragmentCollection;
use Prismic\Document\Fragment\FragmentInterface;
use Prismic\Document\Fragment\Link\DocumentLink;

interface DocumentInterface
{
    public static function fromJsonObject(object $data, Api $api) : DocumentInterface;

    public function getId() : string;

    public function getUid() :? string;

    public function getType() : string;

    /** @return string[] */
    public function getTags() : array;

    public function getFirstPublicationDate() :? DateTimeInterface;

    public function getLastPublicationDate() :? DateTimeInterface;

    public function getLang() :? string;

    public function getHref() : string;

    /** @return object[] */
    public function getAlternateLanguages() : array;

    public function getData() : FragmentCollection;

    /** @return string[] */
    public function getSlugs() : array;

    public function getSlug() :? string;

    public function get(string $key) :? FragmentInterface;

    public function has(string $key) : bool;

    public function asLink() : DocumentLink;
}
