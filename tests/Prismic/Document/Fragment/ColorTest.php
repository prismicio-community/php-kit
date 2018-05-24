<?php
declare(strict_types=1);

namespace Prismic\Test\Document\Fragment;

use Prismic\Document\Fragment\Color;
use Prismic\Test\FakeLinkResolver;
use Prismic\Test\TestCase;

class ColorTest extends TestCase
{

    public function testFactory()
    {
        $linkResolver = new FakeLinkResolver();
        $data = \json_decode('{"type": "Color", "value": "#000000"}');
        $colour = Color::factory($data, $linkResolver);
        $this->assertInstanceOf(Color::class, $colour);

        $colour = Color::factory('#000000', $linkResolver);
        $this->assertInstanceOf(Color::class, $colour);

        $colour = Color::factory(null, $linkResolver);
        $this->assertInstanceOf(Color::class, $colour);
    }

    public function testIsColor()
    {
        $linkResolver = new FakeLinkResolver();
        /** @var Color $colour */
        $colour = Color::factory('#000000', $linkResolver);
        $this->assertTrue($colour->isColor());

        $colour = Color::factory(null, $linkResolver);
        $this->assertFalse($colour->isColor());

        $colour = Color::factory('foo', $linkResolver);
        $this->assertFalse($colour->isColor());
    }

    private function getBlack() : Color
    {
        $linkResolver = new FakeLinkResolver();
        /** @var Color $colour */
        $colour = Color::factory('#000000', $linkResolver);
        return $colour;
    }

    private function getNonColor() : Color
    {
        $linkResolver = new FakeLinkResolver();
        /** @var Color $colour */
        $colour = Color::factory(null, $linkResolver);
        return $colour;
    }

    public function testAsRgb()
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

    public function testAsRgbString()
    {
        $colour = $this->getBlack();
        $this->assertSame('rgb(0, 0, 0)', $colour->asRgbString());
        $this->assertSame('rgba(0, 0, 0, 0.500)', $colour->asRgbString(.5));

        $colour = $this->getNonColor();
        $this->assertNull($colour->asRgbString());
    }

    public function testAsInteger()
    {
        $colour = $this->getBlack();
        $expect = \hexdec('000000');
        $this->assertSame($expect, $colour->asInteger());

        $colour = $this->getNonColor();
        $this->assertNull($colour->asInteger());
    }

}
