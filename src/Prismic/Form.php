<?php
declare(strict_types=1);

namespace Prismic;

use stdClass;

/**
 * Embodies a RESTful form. This is meant for internal use.
 */
class Form
{
    /**
     * Form Name/Label
     *
     * @var string|null
     */
    private $name;

    /**
     * Form Method
     *
     * @var string
     */
    private $method;

    /**
     * The rel if there's one
     *
     * @var string|null
     */
    private $rel;

    /**
     * Encoding type
     *
     * @var string
     */
    private $enctype;

    /**
     * Form Action/URL
     *
     * @var string
     */
    private $action;

    /**
     * The list of Prismic\FieldForm objects that can be used
     *
     * @var array
     */
    private $fields;

    /**
     * Constructs the Form object.
     *
     * @param string $name      the name of the form
     * @param string $method    the method to use
     * @param string $rel       the rel if there's one
     * @param string $enctype   the encoding type
     * @param string $action    the action
     * @param array  $fields    the list of Prismic::FieldForm objects that can be used
     */
    private function __construct(
        ?string $name = null,
        string  $method,
        ?string $rel = null,
        string  $enctype,
        string  $action,
        array   $fields
    ) {
        $this->name    = $name;
        $this->method  = $method;
        $this->rel     = $rel;
        $this->enctype = $enctype;
        $this->action  = $action;
        $this->fields  = $fields;
    }

    /**
     * Initializes the data that will be sent as the API call to a default value.
     *
     * @return array the array of arguments that will be passed
     */
    public function defaultData() : array
    {
        /**
         * @var string    $key
         * @var FieldForm $field
         */
        $dft = array();
        foreach ($this->fields as $key => $field) {
            $default = $field->getDefaultValue();
            if (isset($default)) {
                if ($field->isMultiple()) {
                    $default = array($default);
                }
                $dft[$key] = $default;
            }
        }

        return $dft;
    }

    /**
     * Return a new instance from a JSON string
     */
    public static function withJsonString(string $json) : self
    {
        $data = \json_decode($json);
        return self::withJsonObject($data);
    }

    /**
     * Return a new instance from unserialized JSON
     */
    public static function withJsonObject(stdClass $json) : self
    {
        $fields = [];
        foreach ($json->fields as $name => $field) {
            $default  = isset($field->default)  ? $field->default  : null;
            $multiple = isset($field->multiple) ? $field->multiple : false;
            $fields[$name] = new FieldForm($field->type, $multiple, $default);
        }

        return new self(
            isset($form->name) ? $form->name : null,
            $form->method,
            isset($form->rel) ? $form->rel : null,
            $form->enctype,
            $form->action,
            $fields
        );
    }

    /**
     * Returns the name
     */
    public function getName() :? string
    {
        return $this->name;
    }

    /**
     * Returns the method
     */
    public function getMethod() : string
    {
        return $this->method;
    }

    /**
     * Returns the rel
     */
    public function getRel() :? string
    {
        return $this->rel;
    }

    /**
     * Returns the enctype
     */
    public function getEnctype() : string
    {
        return $this->enctype;
    }

    /**
     * Returns the action
     */
    public function getAction() : string
    {
        return $this->action;
    }

    /**
     * Returns the fields
     */
    public function getFields() : array
    {
        return $this->fields;
    }
}
