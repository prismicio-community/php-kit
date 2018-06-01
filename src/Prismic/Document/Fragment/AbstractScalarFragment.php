<?php
declare(strict_types=1);

namespace Prismic\Document\Fragment;

use Prismic\Exception\InvalidArgumentException;

abstract class AbstractScalarFragment implements FragmentInterface
{
    use HtmlHelperTrait;

    /** @var mixed */
    protected $value;

    protected function __construct()
    {
    }

    public static function factory($value)
    {
        if (\is_object($value) && \property_exists($value, 'value')) {
            $value = $value->value; // V1 API
        }
        if (\is_object($value) || \is_array($value)) {
            throw new InvalidArgumentException(\sprintf(
                'Cannot determine single scalar value from input of type %s with value %s',
                gettype($value),
                \json_encode($value)
            ));
        }

        $fragment = new static();
        $fragment->value = $value;
        return $fragment;
    }

    public function asText() :? string
    {
        // Bools are unlikely, but cast to int first, just in case
        $value = \is_bool($this->value) ? (int) $this->value : $this->value;
        $value = (string) $value;
        return ($value === '') ? null : $value;
    }

    public function asHtml() :? string
    {
        $value = (string) $this->asText();
        return ($value === '')
               ? null
               : $this->escapeHtml($value);
    }

    public function asHtmlAttribute() :? string
    {
        $value = (string) $this->asText();
        return ($value === '')
            ? null
            : $this->escapeHtmlAttr($value);
    }

    public function asInteger() :? int
    {
        $value = $this->asText();
        return ! \is_numeric($value)
               ? null
               : (int) $value;
    }

    public function asFloat() :? float
    {
        $value = $this->asText();
        return ! \is_numeric($value)
            ? null
            : (float) $value;
    }
}
