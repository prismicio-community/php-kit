<?php
declare(strict_types=1);

namespace Prismic\Document\Fragment;

use Prismic\Exception\InvalidArgumentException;
use function count;
use function gettype;
use function implode;
use function sprintf;
use const PHP_EOL;

class ListElement implements CompositeFragmentInterface
{
    /** @var string */
    private $tag;

    /** @var TextElement[] */
    private $items;

    private function __construct()
    {
        $this->items = [];
    }

    public static function fromTag(string $tag) : ListElement
    {
        $element = new static();
        if ($tag !== 'ul' && $tag !== 'ol') {
            throw new InvalidArgumentException(sprintf(
                'Expected the string ul or ol to the named constructor. Received %s',
                gettype($tag)
            ));
        }

        $element->tag = $tag;

        return $element;
    }

    public function getTag() : string
    {
        return $this->tag;
    }

    public function addItem(TextElement $item) : void
    {
        if ($item->getTag() !== 'li') {
            throw new InvalidArgumentException(sprintf(
                'You can only append list items with the "li" tag to a list. Received an element with the tag "%s"',
                $item->getTag()
            ));
        }

        $this->items[] = $item;
    }

    /**
     * @return TextElement[]
     */
    public function getItems() : array
    {
        return $this->items;
    }

    public function hasItems() : bool
    {
        return count($this->items) >= 1;
    }

    public function isOrdered() : bool
    {
        return $this->tag === 'ol';
    }

    public function asText() :? string
    {
        if ($this->hasItems()) {
            $data = [];
            foreach ($this->items as $item) {
                $data[] = $item->asText();
            }

            return implode(PHP_EOL, $data);
        }

        return null;
    }

    public function openTag() :? string
    {
        if ($this->hasItems()) {
            return sprintf('<%s>', $this->tag);
        }

        return null;
    }

    public function closeTag() :? string
    {
        if ($this->hasItems()) {
            return sprintf('</%s>', $this->tag);
        }

        return null;
    }

    public function asHtml() :? string
    {
        if ($this->hasItems()) {
            $data = [];
            $data[] = $this->openTag();
            foreach ($this->items as $item) {
                $data[] = $item->asHtml();
            }

            $data[] = $this->closeTag();

            return implode(PHP_EOL, $data);
        }

        return null;
    }
}
