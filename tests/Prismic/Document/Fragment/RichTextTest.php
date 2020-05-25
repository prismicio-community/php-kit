<?php
declare(strict_types=1);

namespace Prismic\Test\Document\Fragment;

use Prismic\Document\Fragment\RichText;
use Prismic\Exception\InvalidArgumentException;
use Prismic\Json;
use Prismic\Test\FakeLinkResolver;
use Prismic\Test\TestCase;
use function assert;

class RichTextTest extends TestCase
{
    public function testFactoryWillOnlyAcceptArray() : void
    {
        $this->expectException(InvalidArgumentException::class);
        RichText::factory('Foo', new FakeLinkResolver());
    }

    public function testBlocksWithoutTypePropertyWillCauseException() : void
    {
        $value = Json::decode('[
            {
                "foo" : "bar"
            }
        ]', false);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No type can be determined for the rich text fragment');
        RichText::factory($value, new FakeLinkResolver());
    }

    public function testAsTextAndHtmlRenderAsExpected() : void
    {
        $value = Json::decode('[
            {
                "type" : "paragraph",
                "text" : "paragraph"
            },
            {
                "type" : "o-list-item",
                "text" : "Item 1"
            },
            {
                "type" : "o-list-item",
                "text" : "Item 2"
            },
            {
                "type" : "heading1",
                "text" : "Heading"
            }
        ]', false);
        $text = RichText::factory($value, new FakeLinkResolver());
        $expect = "paragraph\nItem 1\nItem 2\nHeading";
        $this->assertSame($expect, $text->asText());

        $expect = "<p>paragraph</p>\n<ol>\n<li>Item 1</li>\n<li>Item 2</li>\n</ol>\n<h1>Heading</h1>";
        $this->assertSame($expect, $text->asHtml());
    }

    public function testFindingByTag() : void
    {
        $value = Json::decode('[
            {
                "type" : "paragraph",
                "text" : "paragraph 1"
            },
            {
                "type" : "o-list-item",
                "text" : "Item 1"
            },
            {
                "type" : "o-list-item",
                "text" : "Item 2"
            },
            {
                "type" : "heading1",
                "text" : "Heading 1"
            },
            {
                "type" : "paragraph",
                "text" : "paragraph 2"
            },
            {
                "type": "image",
                "url": "IMAGE URL",
                "alt": null,
                "copyright": null,
                "dimensions": {
                    "width": 960,
                    "height": 800
                },
                "label": "test-label"
            },
            {
                "type" : "heading2",
                "text" : "Heading 2"
            },
            {
                "type" : "list-item",
                "text" : "Item 1"
            }
        ]', false);
        $text = RichText::factory($value, new FakeLinkResolver());
        assert($text instanceof RichText);

        $paragraph = $text->getFirstParagraph();
        $this->assertSame('paragraph 1', $paragraph->asText());

        $heading = $text->getFirstHeading();
        $this->assertSame('Heading 1', $heading->asText());

        $image = $text->getFirstImage();
        $this->assertSame('IMAGE URL', $image->asText());

        $heading = $text->getFirstByTag('h1');
        $this->assertSame('Heading 1', $heading->asText());

        $heading = $text->getFirstByTag('h2');
        $this->assertSame('Heading 2', $heading->asText());

        $list = $text->getFirstList();
        $this->assertCount(2, $list->getItems());

        $this->assertNull($text->getFirstByTag('whatever'));

        $this->assertCount(2, $text->getParagraphs());
        $this->assertCount(2, $text->getHeadings());
        $this->assertCount(2, $text->getLists());
    }

    public function testFindingByTagOnEmptyStructure() : void
    {
        $text = RichText::factory([], new FakeLinkResolver());
        assert($text instanceof RichText);

        $this->assertNull($text->getFirstParagraph());
        $this->assertNull($text->getFirstHeading());
        $this->assertNull($text->getFirstImage());
        $this->assertNull($text->getFirstList());
        $this->assertCount(0, $text->getImages());
        $this->assertCount(0, $text->getParagraphs());
        $this->assertCount(0, $text->getHeadings());
        $this->assertCount(0, $text->getLists());
    }
}
