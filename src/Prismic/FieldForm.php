<?php

/*
 * This file is part of the Prismic PHP SDK
 *
 * Copyright 2013 Zengularity (http://www.zengularity.com).
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Prismic;

class FieldForm
{

    private $type;
    private $multiple;
    private $defaultValue;

    /**
     * @param string $type
     * @param string $defaultValue
     */
    public function __construct($type, $mutiple, $defaultValue)
    {
        $this->type = $type;
        $this->multiple = $mutiple;
        $this->defaultValue = $defaultValue;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    public function isMultiple()
    {
        return $this->multiple;
    }
}
