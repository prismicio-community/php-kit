<?php
declare(strict_types=1);

namespace Prismic;

class Form
{
    /**
     * The key used to identify the form
     *
     * @var string
     */
    private $key;

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
     * @var FieldForm[]
     */
    private $fields;

    /**
     * @param string      $name    the name of the form
     * @param string      $method  the method to use
     * @param string      $rel     the rel if there's one
     * @param string      $enctype the encoding type
     * @param string      $action  the action
     * @param FieldForm[] $fields  the list of FieldForm objects that can be used
     */
    private function __construct(
        string $key,
        ?string $name,
        string $method,
        ?string $rel,
        string $enctype,
        string $action,
        array $fields
    ) {
        $this->key     = $key;
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
     * @return string[] the array of arguments that will be passed
     */
    public function defaultData() : array
    {
        $defaults = [];
        foreach ($this->fields as $key => $field) {
            $default = $field->getDefaultValue();
            if (! $default) {
                continue;
            }

            $defaults[$key] = $field->isMultiple() ? [$default] : $default;
        }

        return $defaults;
    }

    /**
     * Return a new instance from a JSON string
     */
    public static function withJsonString(string $key, string $json) : self
    {
        return self::withJsonObject(
            $key,
            Json::decodeObject($json)
        );
    }

    /**
     * Return a new instance from unserialized JSON
     */
    public static function withJsonObject(string $key, object $json) : self
    {
        $fields = [];
        foreach ($json->fields as $name => $field) {
            $default  = $field->default ?? null;
            $multiple = $field->multiple ?? false;
            $fields[$name] = new FieldForm($field->type, $multiple, $default);
        }

        return new self(
            $key,
            $json->name ?? $key,
            $json->method,
            $json->rel ?? null,
            $json->enctype,
            $json->action,
            $fields
        );
    }

    public function getKey() : string
    {
        return $this->key;
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
     *
     * @return FieldForm[]
     */
    public function getFields() : array
    {
        return $this->fields;
    }

    /**
     * Return a single field by name
     */
    public function getField(string $name) :? FieldForm
    {
        return $this->fields[$name] ?? null;
    }
}
