<?php
declare(strict_types=1);

namespace Prismic\Test\Document\Fragment;

use Prismic\Document\Fragment\FragmentCollection;
use Prismic\Document\Fragment\RichText;
use Prismic\Test\FakeLinkResolver;
use Prismic\Test\TestCase;

class FragmentCollectionTest extends TestCase
{
    /**
     * @expectedException \Prismic\Exception\InvalidArgumentException
     * @expectedExceptionMessage Expected an object as the collection value
     */
    public function testExceptionThrownWhenFactoryReceivesNonObject()
    {
        FragmentCollection::factory([], new FakeLinkResolver());
    }

    /**
     * @expectedException \Prismic\Exception\UnexpectedValueException
     * @expectedExceptionMessage Cannot determine the fragment type at index
     */
    public function testExceptionThrownByFactoryForUndeterminableType()
    {
        $data = \json_decode('{
            "name": {
                "could" : "be anything"
            }
        }');
        FragmentCollection::factory($data, new FakeLinkResolver());
    }

    public function testSimpleTextAndHtmlRendering()
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

    public function testEmptyCollection()
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
