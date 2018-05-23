<?php
declare(strict_types=1);

namespace Prismic\Document\Fragment;

use Prismic\Exception\InvalidArgumentException;
use Prismic\LinkResolver;

abstract class AbstractScalarFragment implements FragmentInterface
{
    use HtmlHelperTrait;

    /** @var mixed */
    protected $value;

    public static function factory($value, LinkResolver $linkResolver) : FragmentInterface
    {
        if (\is_object($value) && isset($value->value)) {
            $value = $value->value; // V1 API
        }
        if (! \is_scalar($value)) {
            throw new InvalidArgumentException(\sprintf(
                'Cannot determine single scalar value from input of type %s',
                gettype($value)
            ));
        }

        $fragment = new static();
        $fragment->value = $value;
        return $fragment;
    }

    public function asText() :? string
    {
        $value = (string) $this->value;
        return empty($value) ? null : $value;
    }

    public function asHtml() :? string
    {
        $value = $this->asText();
        return empty($value)
               ? null
               : $this->escapeHtml($this->asText());
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
