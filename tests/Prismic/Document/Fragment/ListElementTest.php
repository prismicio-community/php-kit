<?php
declare(strict_types=1);

namespace Prismic\Test\Document\Fragment;

use Prismic\Document\Fragment\ListElement;
use Prismic\Document\Fragment\TextElement;
use Prismic\Test\FakeLinkResolver;
use Prismic\Test\TestCase;

class ListElementTest extends TestCase
{

    /**
     * @expectedException \Prismic\Exception\InvalidArgumentException
     */
    public function testFactoryThrowsExceptionForInvalidTag()
    {
        ListElement::factory('foo', new FakeLinkResolver());
    }

    public function testOrderedAndUnordered()
    {
        $linkResolver = new FakeLinkResolver();
        /** @var ListElement $list */
        $list = ListElement::factory('ul', $linkResolver);
        $this->assertFalse($list->isOrdered());
        $list = ListElement::factory('ol', $linkResolver);
        $this->assertTrue($list->isOrdered());
    }

    public function testEmptyListsReturnNullForTextAndHtml()
    {
        $linkResolver = new FakeLinkResolver();
        /** @var ListElement $list */
        $list = ListElement::factory('ul', $linkResolver);
        $this->assertFalse($list->hasItems());
        $this->assertNull($list->asHtml());
        $this->assertNull($list->asText());
        $this->assertNull($list->openTag());
        $this->assertNull($list->closeTag());
    }

    /**
     * @expectedException \Prismic\Exception\InvalidArgumentException
     */
    public function testExceptionThrowForInvalidListItemType()
    {
        $linkResolver = new FakeLinkResolver();
        $p = TextElement::factory(
            \json_decode('{"type":"paragraph", "text":"Foo"}'),
            $linkResolver
        );
        /** @var ListElement $list */
        $list = ListElement::factory('ul', $linkResolver);
        $list->addItem($p);
    }

    public function testRenderingToHtml()
    {
        $linkResolver = new FakeLinkResolver();
        $item = TextElement::factory(
            \json_decode('{"type":"o-list-item", "text":"Foo"}'),
            $linkResolver
        );
        /** @var ListElement $list */
        $list = ListElement::factory('ol', $linkResolver);
        $list->addItem($item);
        $this->assertTrue($list->hasItems());

        $expect = '<ol><li>Foo</li></ol>';
        $html = \str_replace(\PHP_EOL, '', $list->asHtml());
        $this->assertSame($expect, $html);
        $this->assertSame('Foo', $list->asText());
    }
}
