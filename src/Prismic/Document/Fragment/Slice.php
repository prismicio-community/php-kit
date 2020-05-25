<?php

declare(strict_types=1);

namespace Prismic\Document\Fragment;

use Prismic\Exception\InvalidArgumentException;
use Prismic\LinkResolver;
use function count;
use function implode;
use function sprintf;
use const PHP_EOL;

class Slice implements CompositeFragmentInterface
{
    use HtmlHelperTrait;

    /** @var FragmentCollection */
    private $primary;

    /** @var Group */
    private $group;

    /** @var string */
    private $type;

    /** @var string|null */
    private $label;

    private function __construct(
        string $type,
        FragmentCollection $primary,
        Group $group,
        ?string $label = null
    ) {
        $this->type    = $type;
        $this->label   = $label;
        $this->primary = $primary;
        $this->group   = $group;
    }

    public static function factory(object $value, LinkResolver $linkResolver) : FragmentInterface
    {
        return static::fromJson($value, $linkResolver);
    }

    public static function fromJson(object $value, LinkResolver $linkResolver) : self
    {
        // Type and Label are the same for V1 & V2
        $type    = isset($value->slice_type)
                 ? (string) $value->slice_type
                 : null;
        $label   = isset($value->slice_label)
                 ? (string) $value->slice_label
                 : null;

        if (! $type) {
            throw new InvalidArgumentException('No Slice type could be determined from the payload');
        }

        // V1
        $group   = isset($value->repeat)
                 ? Group::factory($value->repeat, $linkResolver)
                 : null;
        $primary = isset($value->{'non-repeat'})
                 ? FragmentCollection::factory($value->{'non-repeat'}, $linkResolver)
                 : null;
        /**
         * In much older versions of the API (Before "Composite Slices"), slices
         * had a 'value' property which contained the repeatable group
         */
        if (isset($value->value, $value->value->type) && ! $group && $value->value->type === 'Group') {
            $group = Group::factory($value->value, $linkResolver);
        }

        // V2
        $group   = isset($value->items)
                 ? Group::factory($value->items, $linkResolver)
                 : $group;
        $primary = isset($value->primary)
                 ? FragmentCollection::factory($value->primary, $linkResolver)
                 : $primary;

        $group = $group ?: Group::emptyGroup();
        $primary = $primary ?: FragmentCollection::emptyCollection();

        return new static($type, $primary, $group, $label);
    }

    public function getType() : string
    {
        return $this->type;
    }

    public function getLabel() :? string
    {
        return $this->label;
    }

    public function getPrimary() : FragmentCollection
    {
        return $this->primary;
    }

    public function getItems() : Group
    {
        return $this->group;
    }

    public function asText() :? string
    {
        $data = [];
        $primary = $this->primary->asText();
        if ($primary) {
            $data[] = $primary;
        }

        $group = $this->group->asText();
        if ($group) {
            $data[] = $group;
        }

        return count($data) >= 1
            ? implode(PHP_EOL, $data)
            : null;
    }

    public function asHtml() :? string
    {
        $primary = $this->primary->asHtml();
        $group   = $this->group->asHtml();
        if (empty($primary) && empty($group)) {
            return null;
        }

        $attributes = [
            'data-slice-type' => $this->type,
        ];
        if ($this->label) {
            $attributes['class'] = $this->label;
        }

        $data = [
            sprintf('<div%s>', $this->htmlAttributes($attributes)),
        ];

        if ($primary) {
            $data[] = $primary;
        }

        if ($group) {
            $data[] = $group;
        }

        $data[] = '</div>';

        return implode(PHP_EOL, $data);
    }
}
