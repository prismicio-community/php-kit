<?php

namespace Prismic\Test;

use Prismic\Dom\RichText;
use Prismic\Test\FakeLinkResolver;

class RichTextTest extends \PHPUnit_Framework_TestCase
{
    private $richText;
    private $linkResolver;

    protected function setUp()
    {
        $this->richText = json_decode(file_get_contents(__DIR__.'/../fixtures/rich-text.json'));
        $this->linkResolver = new FakeLinkResolver();
    }

    public function testAsText() {
        $expected = "The title\n";
        $actual = RichText::asText($this->richText->title);
        $this->assertEquals($expected, $actual);
    }
}
