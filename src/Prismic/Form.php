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
            $default = $field->getDefaultValue();
            if (isset($default)) {
                if ($field->isMultiple()) {
                    $dft[$key] = array($default);
                } else {
                    $dft[$key] = $default;
                }
            }
        }

        return $dft;
    }

    public function getName()
    {
        return $this->maybeName;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function getRel()
    {
        return $this->maybeRel;
    }

    public function getEnctype()
    {
        return $this->enctype;
    }

    public function getAction()
    {
        return $this->action;
    }

    public function getFields()
    {
        return $this->fields;
    }
}
