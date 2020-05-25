<?php
declare(strict_types=1);

namespace Prismic\Document\Fragment;

use Prismic\Exception\InvalidArgumentException;
use Prismic\Json;
use Prismic\LinkResolver;
use function count;
use function implode;
use function is_array;
use function sprintf;
use const PHP_EOL;

class Group implements CompositeFragmentInterface
{
    /** @var CompositeFragmentInterface[] */
    private $fragments = [];

    /** @param object|mixed[] $value */
    public static function factory($value, LinkResolver $linkResolver) : self
    {
        $value = $value->value ?? $value;
        /**
         * A Group is a zero indexed array of objects/maps. Each element is a fragment,
         */
        if (! is_array($value)) {
            throw new InvalidArgumentException(sprintf(
                'Expected an indexed array for group construction, received %s',
                Json::encode($value)
            ));
        }

        $group = new static();
        foreach ($value as $collection) {
            /**
             * Groups are used to encapsulate either the elements in group which are a collection
             * Or, as the top-level identifier to encapsulate an array of slices, therefore,
             * the resulting array will contain Multiple or single collections when the type is a group
             * or multiple or single slices
             */
            if (isset($collection->slice_type)) {
                $group->fragments[] = Slice::fromJson($collection, $linkResolver);
            } else {
                $group->fragments[] = FragmentCollection::factory($collection, $linkResolver);
            }
        }

        return $group;
    }

    public static function emptyGroup() : self
    {
        return new static();
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

        return implode(PHP_EOL, $data);
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

        return implode(PHP_EOL, $data);
    }

    /**
     * @return Slice[]|FragmentCollection[]|CompositeFragmentInterface[]
     */
    public function getItems() : array
    {
        return $this->fragments;
    }
}
