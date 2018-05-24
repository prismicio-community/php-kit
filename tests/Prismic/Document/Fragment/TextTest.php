<?php
declare(strict_types=1);

namespace Prismic\Test\Document\Fragment;

use Prismic\Document\Fragment\Text;
use Prismic\Test\FakeLinkResolver;
use Prismic\Test\TestCase;

class TextTest extends TestCase
{
    public function invalidSpecProvider() : array
    {
        return [
            ['{}'],
            ['{"value": {"thing": "non-scalar"}}'],
            ['[]'],
        ];
    }

    /**
     * @expectedException \Prismic\Exception\InvalidArgumentException
     * @expectedExceptionMessage Cannot determine single scalar value from input
     * @dataProvider invalidSpecProvider
     */
    public function testFactoryThrowsExceptionForInvalidSpec(string $json)
    {
        $value = \json_decode($json);
        Text::factory($value, new FakeLinkResolver());
    }

    public function testV1Spec()
    {
        $value = \json_decode('{
            "type" : "Text",
            "value": "Some Text"
        }');
        /** @var Text $text */
        $text = Text::factory($value, new FakeLinkResolver());
        $this->assertSame('Some Text', $text->asText());
        $this->assertSame('Some Text', $text->asHtml());
        $this->assertSame('Some&#x20;Text', $text->asHtmlAttribute());
    }

    public function validScalarValueProvider() : array
    {
        return [
            [null, null, null, null, null, null],
            [1, '1', '1', '1', 1, 1.0],
            [1.1, '1.1', '1.1', '1.1', 1, 1.1],
            ['foo', 'foo', 'foo', 'foo', null, null],
            [0, '0', '0', '0', 0, 0.0],
            [true, '1', '1', '1', 1, 1.0],
            [false, '0', '0', '0', 0, 0.0]
        ];
    }

    /**
     * @dataProvider validScalarValueProvider
     */
    public function testValidScalarValues($value, $expectText, $expectHtml, $expectAttribute, $expectInt, $expectFloat)
    {
        /** @var Text $text */
        $text = Text::factory($value, new FakeLinkResolver());
        $this->assertSame($expectText, $text->asText());
        $this->assertSame($expectHtml, $text->asHtml());
        $this->assertSame($expectAttribute, $text->asHtmlAttribute());
        $this->assertSame($expectInt, $text->asInteger());
        $this->assertSame($expectFloat, $text->asFloat());
    }
}
