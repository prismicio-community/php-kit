<?php

declare(strict_types=1);

namespace Prismic\Document\Fragment;

use Prismic\Exception\InvalidArgumentException;
use Prismic\LinkResolver;

class RichText implements CompositeFragmentInterface
{

    /**
     * @var FragmentInterface[]
     */
    private $blocks;

    public static function factory($value, LinkResolver $linkResolver) : FragmentInterface
    {
        $richText = new static;
        // In API V2, Rich text is an array of 'Block Level' objects,
        // in V1, the array is in the property 'value'
        if (isset($value->value)) {
            $value = $value->value;
        }
        if (! \is_array($value)) {
            throw new InvalidArgumentException(sprintf(
                'Expected to be able to determine an array of blocks for the RichText fragment, received %s.',
                \gettype($value)
            ));
        }

        $richText->blocks = [];
        /**
         * Pretty much everything possible is a text element with the exception of images and o-embeds
         * Lists also need additional effort. You cannot have 2 consecutive lists of the same type, so list items will
         * always belong together
         */

        /** @var ListElement|null $openList */
        $openList = null;
        foreach ($value as $blockData) {
            if (! isset($blockData->type)) {
                throw new InvalidArgumentException(\sprintf(
                    'No type can be determined for the rich text fragment with the payload %s',
                    \json_encode($value)
                ));
            }
            $type = $blockData->type;
            switch ($type) {
                case 'heading1':
                case 'heading2':
                case 'heading3':
                case 'heading4':
                case 'heading5':
                case 'heading6':
                case 'paragraph':
                case 'preformatted':
                    $richText->blocks[] = TextElement::factory($blockData, $linkResolver);
                    $openList = null;
                    break;
                case 'image':
                    $richText->blocks[] = Image::factory($blockData, $linkResolver);
                    $openList = null;
                    break;
                case 'embed':
                    $richText->blocks[] = Embed::factory($blockData, $linkResolver);
                    $openList = null;
                    break;
                case 'o-list-item':
                    if (! $openList || $openList->getTag() !== 'ol') {
                        $openList = ListElement::fromTag('ol');
                        $richText->blocks[] = $openList;
                    }
                    /** @var TextElement $item */
                    $item = TextElement::factory($blockData, $linkResolver);
                    $openList->addItem($item);
                    break;
                case 'list-item':
                    if (! $openList || $openList->getTag() !== 'ul') {
                        $openList = ListElement::fromTag('ul');
                        $richText->blocks[] = $openList;
                    }
                    /** @var TextElement $item */
                    $item = TextElement::factory($blockData, $linkResolver);
                    $openList->addItem($item);
                    break;
            }
        }
        return $richText;
    }

    public function asText() :? string
    {
        $data = [];
        foreach ($this->blocks as $block) {
            $data[] = $block->asText();
        }
        return \implode(\PHP_EOL, $data);
    }

    public function asHtml() :? string
    {
        $data = [];
        foreach ($this->blocks as $block) {
            $data[] = $block->asHtml();
        }
        return \implode(\PHP_EOL, $data);
    }

    public function getFirstParagraph() :? TextElement
    {
        return $this->getFirstByTag('p');
    }

    public function getParagraphs()
    {
        return array_filter($this->blocks, function ($block) {
            return ($block instanceof TextElement && $block->getTag() === 'p');
        });
    }

    public function getFirstHeading() :? TextElement
    {
        $headings = $this->getHeadings();
        if (\count($headings)) {
            return \current($headings);
        }
        return null;
    }

    public function getHeadings()
    {
        return array_filter($this->blocks, function ($block) {
            return ($block instanceof TextElement && preg_match('/^h[0-9]{1}$/i', $block->getTag()));
        });
    }

    public function getFirstByTag(string $tag) :? TextElement
    {
        foreach ($this->blocks as $fragment) {
            if ($fragment instanceof TextElement && $fragment->getTag() === $tag) {
                return $fragment;
            }
        }
        return null;
    }

    public function getImages() : array
    {
        $images = [];
        foreach ($this->blocks as $block) {
            if ($block instanceof Image) {
                $images[] = $block;
            }
        }
        return $images;
    }

    public function getFirstImage() :? Image
    {
        foreach ($this->blocks as $block) {
            if ($block instanceof Image) {
                return $block;
            }
        }
        return null;
    }

    public function getLists() : array
    {
        return \array_filter($this->blocks, function ($block) {
            return ($block instanceof ListElement);
        });
    }

    public function getFirstList() :? ListElement
    {
        $lists = $this->getLists();
        return \count($lists) ? \current($lists) : null;
    }
}
