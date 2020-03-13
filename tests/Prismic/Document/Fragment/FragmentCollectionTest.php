<?php
declare(strict_types=1);

namespace Prismic\Test\Document\Fragment;

use Prismic\Document\Fragment\FragmentCollection;
use Prismic\Document\Fragment\RichText;
use Prismic\Exception\InvalidArgumentException;
use Prismic\Exception\UnexpectedValueException;
use Prismic\Test\FakeLinkResolver;
use Prismic\Test\TestCase;

class FragmentCollectionTest extends TestCase
{
    public function testExceptionThrownWhenFactoryReceivesNonObject() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected an object as the collection value');
        FragmentCollection::factory([], new FakeLinkResolver());
    }

    public function testExceptionThrownByFactoryForUnDeterminableType() : void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Cannot determine the fragment type at index');
        $data = \json_decode('{
            "name": {
                "could" : "be anything"
            }
        }');
        FragmentCollection::factory($data, new FakeLinkResolver());
    }

    public function testSimpleTextAndHtmlRendering() : void
    {
        $data = \json_decode('{
            "richtext": [
                {
                    "type": "paragraph",
                    "text": "paragraph 1"
                }
            ]
        }');
        /** @var FragmentCollection $collection */
        $collection = FragmentCollection::factory($data, new FakeLinkResolver());
        $this->assertSame('paragraph 1', $collection->asText());
        $this->assertSame('<p>paragraph 1</p>', $collection->asHtml());

        $this->assertTrue($collection->has('richtext'));
        $p = $collection->get('richtext');
        $this->assertInstanceOf(RichText::class, $p);
    }

    public function testEmptyCollection() : void
    {
        $data = \json_decode('{}');
        /** @var FragmentCollection $collection */
        $collection = FragmentCollection::factory($data, new FakeLinkResolver());
        $this->assertNull($collection->asText());
        $this->assertNull($collection->asHtml());
        $this->assertFalse($collection->has('element'));
        $this->assertNull($collection->get('element'));
    }
}
