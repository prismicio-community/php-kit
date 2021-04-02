<?php
declare(strict_types=1);

namespace Prismic\Test\Dom;

use Prismic\Test\TestCase;
use Prismic\Dom\Link;
use Prismic\Test\FakeLinkResolver;

class LinkTest extends TestCase
{
    private $links;
    private $linkResolver;

    protected function setUp(): void
    {
        $this->links = json_decode($this->getJsonFixture('links.json'));
        $this->linkResolver = new FakeLinkResolver();
    }

    public function testWebLinkAsUrl()
    {
        $expected = 'https://www.youtube.com/watch?v=dQw4w9WgXcQ';
        $actual = Link::asUrl($this->links->web);
        $this->assertEquals($expected, $actual);
    }

    public function testMediaLinkAsUrl()
    {
        $expected = 'https://prismic-io.s3.amazonaws.com/levi-templeting%2Fe57968c2-4536-4548-b720-ebb8f3becbcd_cool-pictures-24.jpg';
        $actual = Link::asUrl($this->links->media);
        $this->assertEquals($expected, $actual);
    }

    public function testDocumentLinkAsUrl()
    {
        $expected = 'http://host/doc/WKb3BSwAACgAb2M4';
        $actual = Link::asUrl($this->links->document, $this->linkResolver);
        $this->assertEquals($expected, $actual);
    }

    public function testDocumentLinkAsUrlWithoutLinkResolver()
    {
        $this->assertNull(Link::asUrl($this->links->document));
    }
}
