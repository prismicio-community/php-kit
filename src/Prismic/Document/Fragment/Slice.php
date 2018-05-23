<?php

declare(strict_types=1);

namespace Prismic\Document\Fragment;

use Prismic\Exception\InvalidArgumentException;
use Prismic\LinkResolver;

class Slice implements CompositeFragmentInterface
{

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

    /** @var string */
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
        // Type and Label are the same for V1 & V2
        $type    = isset($value->slice_type)
                 ? (string) $value->slice_type
                 : null;
        $label   = isset($value->slice_label)
                 ? (string) $value->slice_label
                 : null;
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
        if (! $group && isset($value->value) && isset($value->type) && $value->type === 'Group') {
            $group = Group::factory($value->value, $linkResolver);
        }

        // V2
        $group   = isset($value->items)
                 ? Group::factory($value->items, $linkResolver)
                 : $group;
        $primary = isset($value->primary)
                 ? FragmentCollection::factory($value->primary, $linkResolver)
                 : $primary;

        if (! $type) {
            throw new InvalidArgumentException('No Slice type could be determined from the payload');
        }
        return new static($type, $label, $primary, $group);
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
        $data = [];
        if ($this->primary) {
            $data[] = $this->primary->asHtml();
        }
        if ($this->group) {
            $data[] = $this->group->asHtml();
        }
        return \sprintf(
            '<div data-slice-type="%s">%s</div>',
            $this->type,
            \implode(\PHP_EOL, $data)
        );
    }
}
