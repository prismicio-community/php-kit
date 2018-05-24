<?php
declare(strict_types=1);

namespace Prismic\Document\Fragment;

use Prismic\Document\Fragment\Link\AbstractLink;
use Prismic\Exception\InvalidArgumentException;
use Prismic\Exception\UnexpectedValueException;
use Prismic\LinkResolver;
use stdClass;

class FragmentCollection implements CompositeFragmentInterface
{

    /** @var FragmentInterface[] */
    protected $fragments = [];

    public static function factory($value, LinkResolver $linkResolver) : FragmentInterface
    {
        if (! \is_object($value)) {
            throw new InvalidArgumentException(\sprintf(
                'Expected an object as the collection value, received %s',
                \gettype($value)
            ));
        }
        $data = \get_object_vars($value);
        $collection = new static;
        foreach ($data as $fragmentName => $value) {
            if (\is_object($value) && \property_exists($value, 'type') && \property_exists($value, 'value')) {
                $collection->v1Factory($fragmentName, $value, $linkResolver);
                continue;
            }
            $collection->v2Factory($fragmentName, $value, $linkResolver);
        }

        return $collection;
    }

    private function v1Factory(string $key, stdClass $value, LinkResolver $linkResolver) : void
    {
        $fragment = null;
        switch ($value->type) {
            case 'Image':
                $fragment = Image::factory($value, $linkResolver);
                break;
            case 'Date':
            case 'Timestamp':
                $fragment = Date::factory($value, $linkResolver);
                break;
            case 'Color':
                $fragment = Color::factory($value, $linkResolver);
                break;
            case 'Number':
                $fragment = Number::factory($value, $linkResolver);
                break;
            case 'Text':
            case 'Select':
                $fragment = Text::factory($value, $linkResolver);
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
                $fragment = GeoPoint::factory($value, $linkResolver);
                break;
            case 'Embed':
                $fragment = Embed::factory($value, $linkResolver);
                break;
            case 'Group':
            case 'SliceZone':
                $fragment = Group::factory($value, $linkResolver);
                break;
            case 'Slice':
                $fragment = Slice::factory($value, $linkResolver);
                break;
        }

        $this->fragments[$key] = $fragment;
    }

    private function v2Factory(string $key, $value, LinkResolver $linkResolver) : void
    {
        if (isset($value->dimensions) && \is_object($value->dimensions)) {
            $this->fragments[$key] = Image::factory($value, $linkResolver);
            return;
        }
        if (\is_float($value)) {
            $this->fragments[$key] = Number::factory($value, $linkResolver);
            return;
        }
        if (\is_string($value)) {
            if (\strpos($value, '#') === 0) {
                $this->fragments[$key] = Color::factory($value, $linkResolver);
                return;
            }
            if (\preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}/', $value)) {
                $this->fragments[$key] = Date::factory($value, $linkResolver);
                return;
            }
            $this->fragments[$key] = Text::factory($value, $linkResolver);
            return;
        }
        if (isset($value->link_type)) {
            $this->fragments[$key] = AbstractLink::abstractFactory($value, $linkResolver);
            return;
        }
        if (isset($value->latitude)) {
            $this->fragments[$key] = GeoPoint::factory($value, $linkResolver);
            return;
        }
        if (isset($value->embed_url)) {
            $this->fragments[$key] = Embed::factory($value, $linkResolver);
            return;
        }
        if (isset($value->slice_type)) {
            $this->fragments[$key] = Slice::factory($value, $linkResolver);
            return;
        }
        // Arrays can now only be RichText or Groups
        if (\is_array($value)) {
            $firstElement = current($value);
            // Does it look like RichText?
            if (isset($firstElement->type)) {
                $this->fragments[$key] = RichText::factory($value, $linkResolver);
                return;
            }
            $this->fragments[$key] = Group::factory($value, $linkResolver);
            return;
        }

        throw new UnexpectedValueException(\sprintf(
            'Cannot determine the fragment type at index %s with the content %s',
            $key,
            \json_encode($value)
        ));
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
        return \implode(\PHP_EOL, $data);
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
        return \implode(\PHP_EOL, $data);
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
