<?php
declare(strict_types=1);

namespace Prismic\Test\Document\Fragment;

use Prismic\Document\Fragment\Text;
use Prismic\Exception\InvalidArgumentException;
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
     * @dataProvider invalidSpecProvider
     */
    public function testFactoryThrowsExceptionForInvalidSpec(string $json) : void
    {
        $value = \json_decode($json);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot determine single scalar value from input');
        Text::factory($value);
    }

    public function testV1Spec() : void
    {
        $value = \json_decode('{
            "type" : "Text",
            "value": "Some Text"
        }');
        /** @var Text $text */
        $text = Text::factory($value);
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
    public function testValidScalarValues($value, $expectText, $expectHtml, $expectAttribute, $expectInt, $expectFloat) : void
    {
        /** @var Text $text */
        $text = Text::factory($value);
        $this->assertSame($expectText, $text->asText());
        $this->assertSame($expectHtml, $text->asHtml());
        $this->assertSame($expectAttribute, $text->asHtmlAttribute());
        $this->assertSame($expectInt, $text->asInteger());
        $this->assertSame($expectFloat, $text->asFloat());
    }
}
