<?php
declare(strict_types=1);

namespace Prismic\Document\Fragment;

use Prismic\Exception\InvalidArgumentException;
use Prismic\Json;
use Prismic\LinkResolver;
use function array_key_exists;
use function sprintf;

class TextElement implements FragmentInterface
{
    use HtmlHelperTrait;

    /** @var string[] */
    private $tagMap = [
        'heading1' => 'h1',
        'heading2' => 'h2',
        'heading3' => 'h3',
        'heading4' => 'h4',
        'heading5' => 'h5',
        'heading6' => 'h6',
        'paragraph' => 'p',
        'preformatted' => 'pre',
        'o-list-item' => 'li',
        'list-item' => 'li',
    ];

    /** @var LinkResolver */
    private $linkResolver;

    /** @var string */
    private $type;

    /** @var string|null */
    private $text;

    /** @var mixed[] */
    private $spans;

    /** @var string|null */
    private $label;

    private function __construct()
    {
    }

    public static function factory(object $value, LinkResolver $linkResolver) : self
    {
        $element = new static();
        $type = $value->type ?? null;
        if (! $type || ! array_key_exists($type, $element->tagMap)) {
            throw new InvalidArgumentException(sprintf(
                'No Text Element type can be determined from the payload %s',
                Json::encode($value)
            ));
        }

        $element->linkResolver = $linkResolver;
        $element->text = $value->text ?? null;
        $element->type = $type;
        $element->spans = $value->spans ?? [];
        $element->label = $value->label ?? null;

        return $element;
    }

    public function asText() : ?string
    {
        return $this->text;
    }

    public function withoutFormatting() :? string
    {
        if ($this->text === null) {
            return null;
        }

        return sprintf(
            '%s%s%s',
            $this->openTag(),
            $this->escapeHtml($this->text),
            $this->closeTag()
        );
    }

    public function asHtml() : ?string
    {
        if ($this->text === null) {
            return null;
        }

        return sprintf(
            '%s%s%s',
            $this->openTag(),
            $this->insertSpans($this->text, $this->spans, $this->linkResolver),
            $this->closeTag()
        );
    }

    public function getTag() : string
    {
        return $this->tagMap[$this->type];
    }

    public function openTag() :? string
    {
        $attributes = $this->label
            ? $this->htmlAttributes(['class' => $this->label])
            : '';

        return sprintf(
            '<%s%s>',
            $this->getTag(),
            $attributes
        );
    }

    public function closeTag() :? string
    {
        return sprintf('</%s>', $this->getTag());
    }
}
