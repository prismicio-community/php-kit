<?php
declare(strict_types=1);

namespace Prismic\Test\Document\Fragment;

use Prismic\Document\Fragment\ListElement;
use Prismic\Document\Fragment\TextElement;
use Prismic\Exception\InvalidArgumentException;
use Prismic\Json;
use Prismic\Test\FakeLinkResolver;
use Prismic\Test\TestCase;
use function assert;
use function str_replace;
use const PHP_EOL;

class ListElementTest extends TestCase
{
    public function testFactoryThrowsExceptionForInvalidTag() : void
    {
        $this->expectException(InvalidArgumentException::class);
        ListElement::fromTag('foo');
    }

    public function testOrderedAndUnordered() : void
    {
        $list = ListElement::fromTag('ul');
        assert($list instanceof ListElement);
        $this->assertFalse($list->isOrdered());
        $list = ListElement::fromTag('ol');
        $this->assertTrue($list->isOrdered());
    }

    public function testEmptyListsReturnNullForTextAndHtml() : void
    {
        $list = ListElement::fromTag('ul');
        assert($list instanceof ListElement);
        $this->assertFalse($list->hasItems());
        $this->assertNull($list->asHtml());
        $this->assertNull($list->asText());
        $this->assertNull($list->openTag());
        $this->assertNull($list->closeTag());
    }

    public function testExceptionThrowForInvalidListItemType() : void
    {
        $linkResolver = new FakeLinkResolver();
        $p = TextElement::factory(
            Json::decodeObject('{"type":"paragraph", "text":"Foo"}'),
            $linkResolver
        );
        $list = ListElement::fromTag('ul');
        assert($list instanceof ListElement);
        $this->expectException(InvalidArgumentException::class);
        $list->addItem($p);
    }

    public function testRenderingToHtml() : void
    {
        $linkResolver = new FakeLinkResolver();
        $item = TextElement::factory(
            Json::decodeObject('{"type":"o-list-item", "text":"Foo"}'),
            $linkResolver
        );
        $list = ListElement::fromTag('ol');
        assert($list instanceof ListElement);
        $list->addItem($item);
        $this->assertTrue($list->hasItems());

        $expect = '<ol><li>Foo</li></ol>';
        $html = str_replace(PHP_EOL, '', $list->asHtml());
        $this->assertSame($expect, $html);
        $this->assertSame('Foo', $list->asText());
    }
}
