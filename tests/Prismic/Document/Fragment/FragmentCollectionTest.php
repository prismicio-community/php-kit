<?php
declare(strict_types=1);

namespace Prismic\Test\Document\Fragment;

use Prismic\Document\Fragment\FragmentCollection;
use Prismic\Document\Fragment\RichText;
use Prismic\Exception\UnexpectedValueException;
use Prismic\Json;
use Prismic\Test\FakeLinkResolver;
use Prismic\Test\TestCase;
use function assert;

class FragmentCollectionTest extends TestCase
{
    public function testExceptionThrownByFactoryForUnDeterminableType() : void
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Cannot determine the fragment type at index');
        $data = Json::decodeObject('{
            "name": {
                "could" : "be anything"
            }
        }');
        FragmentCollection::factory($data, new FakeLinkResolver());
    }

    public function testSimpleTextAndHtmlRendering() : void
    {
        $data = Json::decodeObject('{
            "richtext": [
                {
                    "type": "paragraph",
                    "text": "paragraph 1"
                }
            ]
        }');
        $collection = FragmentCollection::factory($data, new FakeLinkResolver());
        assert($collection instanceof FragmentCollection);
        $this->assertSame('paragraph 1', $collection->asText());
        $this->assertSame('<p>paragraph 1</p>', $collection->asHtml());

        $this->assertTrue($collection->has('richtext'));
        $p = $collection->get('richtext');
        $this->assertInstanceOf(RichText::class, $p);
    }

    public function testEmptyCollection() : void
    {
        $data = Json::decodeObject('{}');
        $collection = FragmentCollection::factory($data, new FakeLinkResolver());
        assert($collection instanceof FragmentCollection);
        $this->assertNull($collection->asText());
        $this->assertNull($collection->asHtml());
        $this->assertFalse($collection->has('element'));
        $this->assertNull($collection->get('element'));
    }
}
