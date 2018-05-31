<?php
declare(strict_types=1);

namespace Prismic\Test\Document\Fragment\Link;

use Prismic\Document\Fragment\FragmentCollection;
use Prismic\Document\Fragment\Link\AbstractLink;
use Prismic\Document\Fragment\Link\DocumentLink;
use Prismic\Document\Fragment\Link\FileLink;
use Prismic\Document\Fragment\Link\ImageLink;
use Prismic\Document\Fragment\Link\WebLink;
use Prismic\Document\Fragment\LinkInterface;
use Prismic\Test\FakeLinkResolver;
use Prismic\Test\TestCase;

class LinkTest extends TestCase
{

    private function getLinkCollection() : FragmentCollection
    {
        /** @var FragmentCollection $collection */
        $collection = FragmentCollection::factory(
            \json_decode($this->getJsonFixture('fragments/links.json')),
            new FakeLinkResolver()
        );
        return $collection;
    }

    public function testAbstractFactoryForAllLinkTypes()
    {
        $links = $this->getLinkCollection()->getFragments();
        $this->assertCount(10, $links);
        $this->assertContainsOnlyInstancesOf(LinkInterface::class, $links);
    }

    /**
     * @expectedException \Prismic\Exception\InvalidArgumentException
     * @expectedExceptionMessage Expected a payload describing a link
     */
    public function testAbstractFactoryThrowsExceptionForNoLinkType()
    {
        AbstractLink::abstractFactory('Foo', new FakeLinkResolver());
    }

    /**
     * @expectedException \Prismic\Exception\InvalidArgumentException
     * @expectedExceptionMessage Encountered a V2 Media link but the subtype was neither image, nor document.
     */
    public function testExceptionThrownForUnknownMediaType()
    {
        $data = \json_decode('{
            "link_type": "Media",
            "name": "image.gif",
            "kind": "who-knows!",
            "url": "IMAGE_URL"
        }');
        AbstractLink::abstractFactory($data, new FakeLinkResolver());
    }

    /**
     * @expectedException \Prismic\Exception\InvalidArgumentException
     * @expectedExceptionMessage Cannot determine a link from the given payload
     */
    public function testExceptionThrownForUnknownLinkType()
    {
        $data = \json_decode('{
            "link_type": "Unknown"
        }');
        AbstractLink::abstractFactory($data, new FakeLinkResolver());
    }

    public function testDocumentLinkReturnsExpectedValues()
    {
        /** @var DocumentLink $link */
        $link = $this->getLinkCollection()->get('link-document');
        $this->assertInstanceOf(DocumentLink::class, $link);
        $this->assertFalse($link->isBroken());
        $this->assertNull($link->getTarget());
        $this->assertSame('RESOLVED_LINK', $link->getUrl());
        $this->assertSame('LinkedDocumentId', $link->getId());
        $this->assertSame('document', $link->getType());
        $this->assertInternalType('array', $link->getTags());
        $this->assertSame('document-uid', $link->getUid());
        $this->assertSame('en-gb', $link->getLang());
        $this->assertSame('slug-value', $link->getSlug());

        $expect = '<a href="RESOLVED_LINK" hreflang="en">RESOLVED_LINK</a>';
        $this->assertSame($expect, $link->asHtml());
    }

    public function testBrokenLinkReturnsExpectedValues()
    {
        /** @var DocumentLink $link */
        $link = $this->getLinkCollection()->get('link-broken');
        $this->assertInstanceOf(DocumentLink::class, $link);
        $this->assertTrue($link->isBroken());
        $this->assertNull($link->getTarget());
        $this->assertNull($link->getUrl());

        $this->assertNull($link->asHtml());
        $this->assertNull($link->asText());
        $this->assertNull($link->openTag());
        $this->assertNull($link->closeTag());
    }

    public function testWebLinksReturnsExpectedValues()
    {
        /** @var WebLink $link */
        $link = $this->getLinkCollection()->get('link-web');
        $this->assertInstanceOf(WebLink::class, $link);
        $this->assertFalse($link->isBroken());

        $this->assertSame('WEB_URL', $link->getUrl());
        $this->assertNull($link->getId());
        $this->assertNull($link->getSlug());
        $this->assertCount(0, $link->getTags());
        $this->assertNull($link->getType());
        $this->assertNull($link->getUid());
        $this->assertNull($link->getLang());

        $this->assertSame('WEB_URL', (string) $link);
        $this->assertSame('WEB_URL', $link->asText());
        $this->assertSame('_blank', $link->getTarget());

        $expect = '<a href="WEB_URL" target="_blank" rel="noopener">WEB_URL</a>';
        $this->assertSame($expect, $link->asHtml());
    }

    /**
     * @expectedException \Prismic\Exception\InvalidArgumentException
     * @expectedExceptionMessage Expected value to contain a url property
     */
    public function testWebLinkThrowsExceptionWhenUrlIsNotSet()
    {
        WebLink::linkFactory(
            \json_decode('{
            }'),
            new FakeLinkResolver()
        );
    }

    public function testFileLinkReturnsExpectedValues()
    {
        /** @var FileLink $link */
        $link = $this->getLinkCollection()->get('link-pdf');
        $this->assertInstanceOf(FileLink::class, $link);
        $this->assertFalse($link->isBroken());
        $this->assertInternalType('integer', $link->getFilesize());
        $this->assertSame('file.pdf', $link->getFilename());

        $expect = '<a href="FILE_URL">file.pdf</a>';
        $this->assertSame($expect, $link->asHtml());
    }

    public function testImageLinkReturnsExpectedValues()
    {
        /** @var ImageLink $link */
        $link = $this->getLinkCollection()->get('link-media');
        $this->assertInstanceOf(ImageLink::class, $link);
        $this->assertFalse($link->isBroken());
        $this->assertInternalType('integer', $link->getFilesize());
        $this->assertInternalType('integer', $link->getWidth());
        $this->assertInternalType('integer', $link->getHeight());
        $this->assertSame('image.gif', $link->getFilename());

        $expect = '<a href="IMAGE_URL">image.gif</a>';
        $this->assertSame($expect, $link->asHtml());
    }
}
