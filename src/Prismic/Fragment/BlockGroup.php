<?php

/*
 * This file is part of the Prismic PHP SDK
 *
 * Copyright 2013 Zengularity (http://www.zengularity.com).
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Prismic\Fragment;

class BlockGroup
{
    private $maybeTag;
    private $blocks;

    public function __construct($maybeTag, $blocks)
    {
        $this->maybeTag = $maybeTag;
        $this->blocks = $blocks;
    }

    public function asText()
    {
        return null;
    }

    public function asHtml($linkResolver = null)
    {
        return null;
    }

    public function addBlock($block)
    {
        array_push($this->blocks, $block);
    }

    public function getTag()
    {
        return $this->maybeTag;
    }

    public function getBlocks()
    {
        return $this->blocks;
    }
}
