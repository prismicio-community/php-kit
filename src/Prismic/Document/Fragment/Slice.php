<?php

declare(strict_types=1);

namespace Prismic\Document\Fragment;

use Prismic\Exception\InvalidArgumentException;
use Prismic\LinkResolver;

class Slice implements CompositeFragmentInterface
{

    use HtmlHelperTrait;

    /**
     * @var FragmentCollection|null
     */
    private $primary;

    /**
     * @var Group|null
     */
    private $group;

    /** @var string */
    private $type;

    /** @var string|null */
    private $label;

    private function __construct(
        string $type,
        ?string $label = null,
        ?FragmentCollection $primary = null,
        ?Group $group = null
    ) {
        $this->type    = $type;
        $this->label   = $label;
        $this->primary = $primary;
        $this->group   = $group;
    }

    public static function factory($value, LinkResolver $linkResolver) : FragmentInterface
    {
        return static::fromJson($value, $linkResolver);
    }

    public static function fromJson($value, LinkResolver $linkResolver) : self
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
        if (! $group && isset($value->value) && isset($value->value->type) && $value->value->type === 'Group') {
            $group = Group::factory($value->value, $linkResolver);
        }

        // V2
        $group   = isset($value->items)
                 ? Group::factory($value->items, $linkResolver)
                 : $group;
        $primary = isset($value->primary)
                 ? FragmentCollection::factory($value->primary, $linkResolver)
                 : $primary;

        return new static($type, $label, $primary, $group);
    }

    public function getType() : string
    {
        return $this->type;
    }

    public function getLabel() :? string
    {
        return $this->label;
    }

    public function getPrimary() :? FragmentCollection
    {
        return $this->primary;
    }

    public function getItems() :? Group
    {
        return $this->group;
    }

    public function asText() :? string
    {
        if (! $this->group && ! $this->primary) {
            return null;
        }
        $data = [];
        if ($this->primary) {
            $data[] = $this->primary->asText();
        }
        if ($this->group) {
            $data[] = $this->group->asText();
        }
        return \implode(\PHP_EOL, $data);
    }

    public function asHtml() :? string
    {
        if (! $this->group && ! $this->primary) {
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
        if ($this->primary) {
            $data[] = $this->primary->asHtml();
        }
        if ($this->group) {
            $data[] = $this->group->asHtml();
        }
        $data[] = '</div>';
        return \implode(\PHP_EOL, $data);
    }
}
