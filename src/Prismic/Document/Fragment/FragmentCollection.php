<?php
declare(strict_types=1);

namespace Prismic\Document\Fragment;

use Prismic\Document\Fragment\Link\AbstractLink;
use Prismic\Exception\InvalidArgumentException;
use Prismic\Exception\UnexpectedValueException;
use Prismic\LinkResolver;
use function count;
use function get_object_vars;
use function gettype;
use function implode;
use function is_array;
use function is_float;
use function is_object;
use function is_string;
use function json_encode;
use function preg_match;
use function property_exists;
use function sprintf;
use function strpos;

class FragmentCollection implements CompositeFragmentInterface
{

    /** @var FragmentInterface[] */
    protected $fragments = [];

    public static function factory($value, LinkResolver $linkResolver) : self
    {
        if (! is_object($value)) {
            throw new InvalidArgumentException(sprintf(
                'Expected an object as the collection value, received %s',
                gettype($value)
            ));
        }
        $data = get_object_vars($value);
        $collection = new static;
        foreach ($data as $fragmentName => $value) {
            if (is_object($value) && property_exists($value, 'type') && property_exists($value, 'value')) {
                $collection->v1Factory($fragmentName, $value, $linkResolver);
                continue;
            }
            $collection->v2Factory($fragmentName, $value, $linkResolver);
        }

        return $collection;
    }

    public static function emptyCollection() : self
    {
        return new static();
    }

    private function v1Factory(string $key, $value, LinkResolver $linkResolver) : void
    {
        $fragment = null;
        switch ($value->type) {
            case 'Image':
                $fragment = Image::factory($value, $linkResolver);
                break;
            case 'Date':
            case 'Timestamp':
                $fragment = Date::factory($value);
                break;
            case 'Color':
                $fragment = Color::factory($value);
                break;
            case 'Number':
                $fragment = Number::factory($value);
                break;
            case 'Text':
            case 'Select':
                $fragment = Text::factory($value);
                break;
            case 'Link.document':
            case 'Link.image':
            case 'Link.web':
            case 'Link.file':
                $fragment = AbstractLink::abstractFactory($value, $linkResolver);
                break;
            case 'StructuredText':
                $fragment = RichText::factory($value, $linkResolver);
                break;
            case 'GeoPoint':
                $fragment = GeoPoint::factory($value);
                break;
            case 'Embed':
                $fragment = Embed::factory($value);
                break;
            case 'Group':
            case 'SliceZone':
                $fragment = Group::factory($value, $linkResolver);
                break;
        }

        $this->fragments[$key] = $fragment;
    }

    private function v2Factory(string $key, $value, LinkResolver $linkResolver) : void
    {
        if ($this->isEmptyFragment($value)) {
            return;
        }

        if (isset($value->dimensions) && is_object($value->dimensions)) {
            $this->fragments[$key] = Image::factory($value, $linkResolver);
            return;
        }
        if (is_bool($value)) {
            $this->fragments[$key] = Boolean::factory($value);
            return;
        }
        if (is_float($value)) {
            $this->fragments[$key] = Number::factory($value);
            return;
        }
        if (is_string($value)) {
            if (strpos($value, '#') === 0) {
                $this->fragments[$key] = Color::factory($value);
                return;
            }
            if (preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}/', $value)) {
                $this->fragments[$key] = Date::factory($value);
                return;
            }
            $this->fragments[$key] = Text::factory($value);
            return;
        }
        if (isset($value->link_type)) {
            $link = AbstractLink::abstractFactory($value, $linkResolver);
            if ($link) {
                $this->fragments[$key] = $link;
            }
            return;
        }
        if (isset($value->latitude)) {
            $this->fragments[$key] = GeoPoint::factory($value);
            return;
        }
        if (isset($value->embed_url)) {
            $this->fragments[$key] = Embed::factory($value);
            return;
        }
        if (isset($value->slice_type)) {
            $this->fragments[$key] = Slice::factory($value, $linkResolver);
            return;
        }
        // Arrays can now only be RichText or Groups
        if (is_array($value)) {
            $firstElement = \current($value);
            // Does it look like RichText?
            if (isset($firstElement->type)) {
                $this->fragments[$key] = RichText::factory($value, $linkResolver);
                return;
            }
            $this->fragments[$key] = Group::factory($value, $linkResolver);
            return;
        }

        if ($this->isEmptyObject($value)) {
            return;
        }

        throw new UnexpectedValueException(sprintf(
            'Cannot determine the fragment type at index %s with the content %s',
            $key,
            json_encode($value)
        ));
    }

    private function isEmptyFragment($value) : bool
    {
        if (null === $value) {
            return true;
        }
        if (is_array($value) && empty($value)) {
            return true;
        }
        if (is_string($value) && '' === $value) {
            return true;
        }

        return false;
    }

    private function isEmptyObject($value) : bool
    {
        if (is_object($value)) {
            /** @var object $value */
            $properties = get_object_vars($value);
            foreach ($properties as $name => $property) {
                if (! $this->isEmptyObject($property)) {
                    return false;
                }
            }
            return true;
        }

        return $this->isEmptyFragment($value);
    }

    public function asText() :? string
    {
        $data = [];
        foreach ($this->fragments as $fragment) {
            $data[] = $fragment->asText();
        }
        if (! count($data)) {
            return null;
        }
        return implode(\PHP_EOL, $data);
    }

    public function asHtml() :? string
    {
        $data = [];
        foreach ($this->fragments as $fragment) {
            $data[] = $fragment->asHtml();
        }
        if (! count($data)) {
            return null;
        }
        return implode(\PHP_EOL, $data);
    }

    public function get(string $key) :? FragmentInterface
    {
        return $this->has($key)
            ? $this->fragments[$key]
            : null;
    }

    public function has(string $key) : bool
    {
        return isset($this->fragments[$key]);
    }

    /** @return FragmentInterface[] */
    public function getFragments() : array
    {
        return $this->fragments;
    }
}
