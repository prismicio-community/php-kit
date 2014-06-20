<?php
/**
 * This file is part of the Prismic PHP SDK
 *
 * Copyright 2013 Zengularity (http://www.zengularity.com).
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Prismic;

class LinkedDocument
{
    private $id;
    private $slug;
    private $type;
    private $tags;

    public function __construct($id, $slug, $type, $tags)
    {
        $this->id = $id;
        $this->slug = $slug;
        $this->type = $type;
        $this->tags = $tags;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getSlug()
    {
        return $this->slug;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getTags()
    {
        return $this->tags;
    }

    public static function parse($json)
    {
        return new LinkedDocument(
            $json->id,
            isset($json->{'slug'}) ? $json->slug : null,
            $json->type,
            $json->tags
        );
    }
}
