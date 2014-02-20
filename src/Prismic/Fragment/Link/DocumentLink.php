<?php

/*
 * This file is part of the Prismic PHP SDK
 *
 * Copyright 2013 Zengularity (http://www.zengularity.com).
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Prismic\Fragment\Link;

class DocumentLink implements LinkInterface
{
    private $id;
    private $type;
    private $tags;
    private $slug;
    private $isBroken;

    public function __construct($id, $type, $tags, $slug, $isBroken)
    {
        $this->id = $id;
        $this->type = $type;
        $this->tags = $tags;
        $this->slug = $slug;
        $this->isBroken = $isBroken;
    }

    public function asHtml($linkResolver)
    {
        return '<a href="' . $linkResolver($this) . '">' . $this->slug . '</a>';
    }

    public static function parse($json)
    {
        return new DocumentLink(
            $json->document->id,
            $json->document->type,
            isset($json->document->{'tags'}) ? $json->document->tags : null,
            $json->document->slug,
            $json->isBroken
        );
    }

    public function getId()
    {
        return $this->id;
    }

    public function asText()
    {
        return $this->id;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getTags()
    {
        return $this->tags;
    }

    public function getSlug()
    {
        return $this->slug;
    }

    public function isBroken()
    {
        return $this->isBroken;
    }
}
