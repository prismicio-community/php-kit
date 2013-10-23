<?php

/*
 * This file is part of the Prismic PHP SDK
 *
 * Copyright 2013 Zengularity (http://www.zengularity.com).
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Prismic\Fragment\Block;

class EmbedBlock implements BlockInterface
{
    private $obj;

    public function __construct($obj)
    {
        $this->obj = $obj;
    }

    public function getObj()
    {
        return $this->obj;
    }
}
