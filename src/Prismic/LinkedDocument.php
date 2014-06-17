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
    private $type;
    private $tags;

    public function __construct($id, $type, $tags)
    {
        $this->id = $id;
        $this->type = $type;
        $this->tags = $tags;
    }

    public function getId()
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

    public static function parse($json)
    {
        return new LinkedDocument(
            $json->id,
            $json->type,
            $json->tags
        );
    }
}
