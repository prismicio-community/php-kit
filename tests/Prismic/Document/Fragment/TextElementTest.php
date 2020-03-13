<?php
declare(strict_types=1);

namespace Prismic\Test\Document\Fragment;

use Prismic\Document\Fragment\TextElement;
use Prismic\Exception\InvalidArgumentException;
use Prismic\Test\FakeLinkResolver;
use Prismic\Test\TestCase;

class TextElementTest extends TestCase
{
    public function testFactoryThrowsExceptionWhenValueHasNoType() : void
    {
        $this->expectException(InvalidArgumentException::class);
        TextElement::factory(
            \json_decode('{"foo":"bar"}'),
            new FakeLinkResolver()
        );
    }

    public function testFactoryThrowsExceptionWhenTypeIsUnknown() : void
    {
        $this->expectException(InvalidArgumentException::class);
        TextElement::factory(
            \json_decode('{"type":"unknown"}'),
            new FakeLinkResolver()
        );
    }

    public function testSpanWithEmptyType() : void
    {
        $value = \json_decode('{
            "type": "paragraph",
            "text": "Paragraph.",
            "spans": [
                {
                    "type" : null
                }
            ]
        }');
        $text = TextElement::factory($value, new FakeLinkResolver());
        $expect = '<p>Paragraph.</p>';
        $this->assertSame($expect, $text->asHtml());
    }

    public function testBoldText() : void
    {
        $value = \json_decode('{
            "type": "paragraph",
            "text": "Paragraph with bold text.",
            "spans": [
                {
                    "start": 15,
                    "end": 24,
                    "type": "strong"
                }
            ]
        }');
        $text = TextElement::factory($value, new FakeLinkResolver());
        $expect = '<p>Paragraph with <strong>bold text</strong>.</p>';
        $this->assertSame($expect, $text->asHtml());
    }

    public function testSpanAroundUtf8Characters() : void
    {
        $value = \json_decode('{
            "type": "paragraph",
            "text": "Unicode paragraph with ßold tex†.",
            "spans": [
                {
                    "start": 23,
                    "end": 32,
                    "type": "strong"
                }
            ]
        }');
        $text = TextElement::factory($value, new FakeLinkResolver());
        $expect = '<p>Unicode paragraph with <strong>ßold tex†</strong>.</p>';
        $this->assertSame($expect, $text->asHtml());
    }

    public function testNestedSpans() : void
    {
        $value = \json_decode('{
            "type": "paragraph",
            "text": "Paragraph with nested spans of several types.",
            "spans": [
                {
                    "start": 10,
                    "end": 44,
                    "type": "strong"
                },
                {
                    "start": 15,
                    "end": 27,
                    "type": "label",
                    "data": {
                        "label": "test-label"
                    }
                },
                {
                    "start": 31,
                    "end": 38,
                    "type": "em"
                }
            ]
        }');
        $text = TextElement::factory($value, new FakeLinkResolver());
        $expect = '<p>Paragraph <strong>with <span class="test-label">nested spans</span> of <em>several</em> types</strong>.</p>';
        $this->assertSame($expect, $text->asHtml());
        $this->assertSame('Paragraph with nested spans of several types.', $text->asText());
    }

    public function testNestedSpansAtTheSameIndex() : void
    {
        $value = \json_decode('{
            "type": "paragraph",
            "text": "Paragraph with multiple spans at the same index.",
            "spans": [
                {
                    "start": 24,
                    "end": 47,
                    "type": "label",
                    "data": {
                        "label": "test-label"
                    }
                },
                {
                    "start": 24,
                    "end": 47,
                    "type": "em"
                },
                {
                    "start": 24,
                    "end": 47,
                    "type": "strong"
                }
            ]
        }');
        /** @var TextElement $text */
        $text = TextElement::factory($value, new FakeLinkResolver());
        $expect = '<p>Paragraph with multiple <strong><em><span class="test-label">spans at the same index</span></em></strong>.</p>';
        $this->assertSame($expect, $text->asHtml());
        $this->assertSame('Paragraph with multiple spans at the same index.', $text->asText());
        $this->assertSame('<p>Paragraph with multiple spans at the same index.</p>', $text->withoutFormatting());
    }

    public function testLabelAtBlockLevel() : void
    {
        $value = \json_decode('{
            "type": "paragraph",
            "text": "Paragraph labelled as a block.",
            "spans": [],
            "label": "test-label"
        }');
        $text = TextElement::factory($value, new FakeLinkResolver());
        $expect = '<p class="test-label">Paragraph labelled as a block.</p>';
        $this->assertSame($expect, $text->asHtml());
        $this->assertSame('Paragraph labelled as a block.', $text->asText());
    }

    public function testNullTextWillRenderAsNull() : void
    {
        $value = \json_decode('{
            "type": "paragraph",
            "text": null,
            "spans": [],
            "label": "test-label"
        }');
        /** @var TextElement $text */
        $text = TextElement::factory($value, new FakeLinkResolver());
        $this->assertNull($text->asText());
        $this->assertNull($text->asHtml());
        $this->assertNull($text->withoutFormatting());
    }

    public function testLinkSpan() : void
    {
        $value = \json_decode('{
            "type": "paragraph",
            "text": "Paragraph with a link to another document.",
            "spans": [
                {
                    "start": 17,
                    "end": 41,
                    "type": "hyperlink",
                    "data": {
                        "link_type": "Web",
                        "url": "URL"
                    }
                }
            ]
        }');
        /** @var TextElement $text */
        $text = TextElement::factory($value, new FakeLinkResolver());
        $expect = '<p>Paragraph with a <a href="URL">link to another document</a>.</p>';
        $this->assertSame($expect, $text->asHtml());
    }

    public function testNewLinesAreConvertedToLineBreaksWhenSpansArePresent() : void
    {
        $value = \json_decode('{
            "type": "paragraph",
            "text": "Paragraph with\nbold\ntext.",
            "spans": [
                {
                    "start": 15,
                    "end": 24,
                    "type": "strong"
                }
            ]
        }');
        $text = TextElement::factory($value, new FakeLinkResolver());
        $expect = "<p>Paragraph with<br />\n<strong>bold<br />\ntext</strong>.</p>";
        $this->assertSame($expect, $text->asHtml());
    }

    public function testNewLinesAreConvertedToLineBreaksWhenSpansAreNotPresent() : void
    {
        $value = \json_decode('{
            "type": "paragraph",
            "text": "Paragraph with\nbold\ntext.",
            "spans": []
        }');
        $text = TextElement::factory($value, new FakeLinkResolver());
        $expect = "<p>Paragraph with<br />\nbold<br />\ntext.</p>";
        $this->assertSame($expect, $text->asHtml());
    }
}
