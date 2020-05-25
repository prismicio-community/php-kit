<?php
declare(strict_types=1);

namespace Prismic\Test\Document\Fragment;

use Prismic\Document\Fragment\Text;
use Prismic\Exception\InvalidArgumentException;
use Prismic\Json;
use Prismic\Test\TestCase;
use function assert;

class TextTest extends TestCase
{
    /** @return mixed[] */
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
        $value = Json::decode($json, false);
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot determine single scalar value from input');
        Text::factory($value);
    }

    public function testV1Spec() : void
    {
        $value = Json::decodeObject('{
            "type" : "Text",
            "value": "Some Text"
        }');
        $text = Text::factory($value);
        assert($text instanceof Text);
        $this->assertSame('Some Text', $text->asText());
        $this->assertSame('Some Text', $text->asHtml());
        $this->assertSame('Some&#x20;Text', $text->asHtmlAttribute());
    }

    /** @return mixed[] */
    public function validScalarValueProvider() : array
    {
        return [
            [null, null, null, null, null, null],
            [1, '1', '1', '1', 1, 1.0],
            [1.1, '1.1', '1.1', '1.1', 1, 1.1],
            ['foo', 'foo', 'foo', 'foo', null, null],
            [0, '0', '0', '0', 0, 0.0],
            [true, '1', '1', '1', 1, 1.0],
            [false, '0', '0', '0', 0, 0.0],
        ];
    }

    /**
     * @param mixed $value
     *
     * @dataProvider validScalarValueProvider
     */
    public function testValidScalarValues($value, ?string $expectText, ?string $expectHtml, ?string $expectAttribute, ?int $expectInt, ?float $expectFloat) : void
    {
        $text = Text::factory($value);
        assert($text instanceof Text);
        $this->assertSame($expectText, $text->asText());
        $this->assertSame($expectHtml, $text->asHtml());
        $this->assertSame($expectAttribute, $text->asHtmlAttribute());
        $this->assertSame($expectInt, $text->asInteger());
        $this->assertSame($expectFloat, $text->asFloat());
    }
}
