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

/**
 * Embodies a field of a RESTful form. This is meant for internal use.
 */
class FieldForm
{

    /**
     * @var string the type of the field
     */
    private $type;
    /**
     * @var boolean can the parameter be used multiple times?
     */
    private $multiple;
    /**
     * @var string the default value
     */
    private $defaultValue;

    /**
     * Constructing a FieldForm.
     *
     * @param string  $type         the type of the field
     * @param boolean $multiple     can the parameter be used multiple times
     * @param string  $defaultValue the default value
     */
    public function __construct($type, $multiple, $defaultValue)
    {
        $this->type = $type;
        $this->multiple = $multiple;
        $this->defaultValue = $defaultValue;
    }

    /**
     * Returns the type of the field.
     *
     * @return string the type of the field.
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Returns the default value.
     *
     * @return string the default value.
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * Returns whether the parameter can be used multiple times.
     *
     * @return boolean true if the paramater can be used multiple times, false otherwise.
     */
    public function isMultiple()
    {
        return $this->multiple;
    }
}
