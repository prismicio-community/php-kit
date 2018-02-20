<?php
declare(strict_types=1);

namespace Prismic;

class FieldForm
{

    /**
     * Field Type
     * @var string
     */
    private $type;

    /**
     * Can the field be used multiple times?
     * @var bool
     */
    private $multiple;

    /**
     * Default Value
     * @var string|null
     */
    private $defaultValue;

    /**
     * Constructing a FieldForm.
     *
     * @param string  $type         the type of the field
     * @param boolean $multiple     can the parameter be used multiple times
     * @param string  $defaultValue the default value
     */
    public function __construct(string $type, bool $multiple, ?string $defaultValue)
    {
        $this->type = $type;
        $this->multiple = $multiple;
        $this->defaultValue = $defaultValue;
    }

    /**
     * Returns the type of the field.
     */
    public function getType() : string
    {
        return $this->type;
    }

    /**
     * Returns the default value.
     */
    public function getDefaultValue() :? string
    {
        return $this->defaultValue;
    }

    /**
     * Returns whether the parameter can be used multiple times.
     */
    public function isMultiple() : bool
    {
        return $this->multiple;
    }
}
