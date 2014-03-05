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

class ImageBlock implements BlockInterface
{

    protected $view;

    public function __construct($view)
    {
        $this->view = $view;
    }

    public function getView()
    {
        return $this->view;
    }
}
