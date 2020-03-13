<?php
declare(strict_types=1);

namespace Prismic\Test;

use Prismic\Document\Fragment\FragmentCollection;

class LinkResolverAbstractTest extends TestCase
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

    public function testInvokeProxiesToResolve() : void
    {
        $resolver = new FakeLinkResolver();
        $links = $this->getLinkCollection();
        $link = $links->get('link-web');
        $url = $resolver($link);
        $this->assertSame('WEB_URL', $url);
    }

    public function testBrokenLinksAreSkipped() : void
    {
        $resolver = new FakeLinkResolver();
        $links = $this->getLinkCollection();
        $link = $links->get('link-broken');
        $this->assertNull($resolver($link));
    }

    public function testDocumentLinksAreResolved() : void
    {
        $resolver = new FakeLinkResolver();
        $links = $this->getLinkCollection();
        $link = $links->get('link-document');
        $url = $resolver($link);
        $this->assertSame('RESOLVED_LINK', $url);
    }
}
