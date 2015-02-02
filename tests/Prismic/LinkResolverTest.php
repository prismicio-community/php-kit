<?php

namespace Prismic\Test;

use Prismic\API;
use Prismic\Cache\ApcCache;
use Prismic\Document;
use Prismic\Fragment\Link\DocumentLink;

class LinkResolverTest extends \PHPUnit_Framework_TestCase
{
    protected $document;

    protected function setUp()
    {
        $cache = new ApcCache();
        $cache->clear();
        $this->linkResolver = new FakeLinkResolver();
        $this->id = 'Ue0EDd_mqb8Dhk3j';
        $type = 'product';
        $tags = array('macaron');
        $slugs = array('ABCD');
        $isBroken = false;
        $href = "http://myrepo.prismic.io/Ue0EDd_mqb8Dhk3j";
        $this->document = new Document($this->id, null, $type, $href, $tags, $slugs, array());
        $this->link = new DocumentLink($this->id, null, $type, $tags, $slugs[0], array(), $isBroken);
        $response = $this->getMockBuilder('Ivory\HttpAdapter\Message\Response')
            ->disableOriginalConstructor()
            ->getMock();
        $response->expects($this->once())->method('getBody')->will($this->returnValue(file_get_contents(__DIR__.'/../fixtures/data.json')));

        $httpAdapter = $this->getMock('Ivory\HttpAdapter\HttpAdapterInterface');
        $httpAdapter->expects($this->any())->method('get')->will($this->returnValue($response));

        $this->api = Api::get('don\'t care about this value', null, $httpAdapter, $cache);
    }

    public function testResolveDocumentLink()
    {
        $content = '<a href="http://host/doc/Ue0EDd_mqb8Dhk3j">ABCD</a>';
        $this->assertEquals($content, $this->link->asHtml($this->linkResolver));
    }

    public function testResolve()
    {
        $content = 'http://host/doc/Ue0EDd_mqb8Dhk3j';
        $this->assertEquals($content, $this->linkResolver->resolve($this->link));
    }

    public function testCall()
    {
        $content = 'http://host/doc/Ue0EDd_mqb8Dhk3j';
        $linkResolver = $this->linkResolver;
        $this->assertEquals($content, $linkResolver($this->link));
    }

    public function testResolveDocument()
    {
        $content = "http://host/doc/Ue0EDd_mqb8Dhk3j";
        $this->assertEquals($content, $this->linkResolver->resolveDocument($this->document));
    }

    public function testResolveLink()
    {
        $content = "http://host/doc/Ue0EDd_mqb8Dhk3j";
        $this->assertEquals($content, $this->linkResolver->resolveLink($this->link));
    }

    public function testIsBookmarkNotFound()
    {
        $bookmark = "macaron_d_or";
        $this->assertFalse($this->linkResolver->isBookmark($this->api, $this->link, $bookmark));
    }

    public function testIsBookmarkFound()
    {
        $bookmark = "about";
        $this->assertTrue($this->linkResolver->isBookmark($this->api, $this->link, $bookmark));
    }

    public function testIsBookmarkDocumentNotFound()
    {
        $bookmark = "macaron_d_or";
        $this->assertFalse($this->linkResolver->isBookmarkDocument($this->api, $this->document, $bookmark));
    }

    public function testIsBookmarkDocument()
    {
        $bookmark = "about";
        $this->assertTrue($this->linkResolver->isBookmarkDocument($this->api, $this->document, $bookmark));
    }

}
