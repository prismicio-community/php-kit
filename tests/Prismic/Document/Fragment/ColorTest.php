<?php
declare(strict_types=1);

namespace Prismic\Test\Document\Fragment;

use Prismic\Document\Fragment\Color;
use Prismic\Json;
use Prismic\Test\TestCase;
use function assert;
use function hexdec;

class ColorTest extends TestCase
{
    public function testFactory() : void
    {
        $data = Json::decodeObject('{"type": "Color", "value": "#000000"}');
        $colour = Color::factory($data);
        $this->assertInstanceOf(Color::class, $colour);

        $colour = Color::factory('#000000');
        $this->assertInstanceOf(Color::class, $colour);

        $colour = Color::factory(null);
        $this->assertInstanceOf(Color::class, $colour);
    }

    public function testIsColor() : void
    {
        $colour = Color::factory('#000000');
        assert($colour instanceof Color);
        $this->assertTrue($colour->isColor());

        $colour = Color::factory(null);
        $this->assertFalse($colour->isColor());

        $colour = Color::factory('foo');
        $this->assertFalse($colour->isColor());
    }

    private function getBlack() : Color
    {
        $colour = Color::factory('#000000');
        assert($colour instanceof Color);

        return $colour;
    }

    private function getNonColor() : Color
    {
        $colour = Color::factory(null);
        assert($colour instanceof Color);

        return $colour;
    }

    public function testAsRgb() : void
    {
        $colour = $this->getBlack();
        $expect = [
            'r' => 0,
            'g' => 0,
            'b' => 0,
        ];
        $this->assertSame($expect, $colour->asRgb());

        $colour = $this->getNonColor();
        $this->assertNull($colour->asRgb());
    }

    public function testAsRgbString() : void
    {
        $colour = $this->getBlack();
        $this->assertSame('rgb(0, 0, 0)', $colour->asRgbString());
        $this->assertSame('rgba(0, 0, 0, 0.500)', $colour->asRgbString(.5));

        $colour = $this->getNonColor();
        $this->assertNull($colour->asRgbString());
    }

    public function testAsInteger() : void
    {
        $colour = $this->getBlack();
        $expect = hexdec('000000');
        $this->assertSame($expect, $colour->asInteger());

        $colour = $this->getNonColor();
        $this->assertNull($colour->asInteger());
    }
}
