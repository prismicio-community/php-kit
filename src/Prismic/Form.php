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

class Form
{
    private $maybeName;
    private $method;
    private $maybeRel;
    private $enctype;
    private $action;
    private $fields;

    /**
     * @param string    $maybeName
     * @param string    $method
     * @param string    $maybeRel
     * @param string    $enctype
     * @param string    $action
     * @param \stdClass $fields
     */
    public function __construct($maybeName, $method, $maybeRel, $enctype, $action, $fields)
    {
        $this->maybeName = $maybeName;
        $this->method = $method;
        $this->maybeRel = $maybeRel;
        $this->enctype = $enctype;
        $this->action = $action;
        $this->fields = $fields;
    }

    public function defaultData()
    {
        $dft = array();
        foreach ($this->fields as $key => $field) {
            if (property_exists($field, "default")) {
                $dft[$key] = $field->default;
            }
        }

        return $dft;
    }

    /**
     * @param string $property
     *
     * @return mixed
     */
    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }
}