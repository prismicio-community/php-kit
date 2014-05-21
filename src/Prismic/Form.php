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
 * Embodies a RESTful form. This is meant for internal use.
 */
class Form
{
    /**
     * @var string the name if there's one
     */
    private $maybeName;
    /**
     * @var string the method to use
     */
    private $method;
    /**
     * @var string the rel if there's one
     */
    private $maybeRel;
    /**
     * @var string the encoding type
     */
    private $enctype;
    /**
     * @var string the action
     */
    private $action;
    /**
     * @var array the list of Prismic\FieldForm objects that can be used
     */
    private $fields;

    /**
     * Constructs the Form object.
     *
     * @param string    $maybeName the name if there's one
     * @param string    $method    the method to use
     * @param string    $maybeRel  the rel if there's one
     * @param string    $enctype   the encoding type
     * @param string    $action    the action
     * @param \stdClass $fields    the list of Prismic\FieldForm objects that can be used
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

    /**
     * Initializes the data that will be sent as the API call to a default value.
     *
     * @return array the array of arguments that will be passed
     */
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

    /**
     * Returns the name
     *
     * @return string the name
     */
    public function getName()
    {
        return $this->maybeName;
    }

    /**
     * Returns the method
     *
     * @return string the method
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Returns the rel
     *
     * @return string the rel
     */
    public function getRel()
    {
        return $this->maybeRel;
    }

    /**
     * Returns the enctype
     *
     * @return string the enctype
     */
    public function getEnctype()
    {
        return $this->enctype;
    }

    /**
     * Returns the action
     *
     * @return string the action
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Returns the fields
     *
     * @return string the fields
     */
    public function getFields()
    {
        return $this->fields;
    }
}
