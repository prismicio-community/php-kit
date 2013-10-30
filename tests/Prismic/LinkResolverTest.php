<?php

namespace Prismic\Test;

use Prismic\API;
use Prismic\Document;
use Prismic\Fragment\Link\DocumentLink;
use Prismic\LinkResolver;

class FakeLinkResolver extends LinkResolver {
    public function resolve($link) {
        return "http://host/doc/".$link->getId();
    }
}

class LinkResolverTest extends \PHPUnit_Framework_TestCase
{
    protected $document;

    protected function setUp()
    {
        $this->linkResolver = new FakeLinkResolver();
        $this->id = 'Ue0EDd_mqb8Dhk3j';
        $type = 'product';
        $tags = ['macaron'];
        $slug = 'ABCD';
        $isBroken = false;
        $href = "http://myrepo.prismic.io/Ue0EDd_mqb8Dhk3j";
        $this->document = new Document($this->id, $type, $href, $tags, $slugs, array());
        $this->link = new DocumentLink($this->id, $type, $tags, $slug, $isBroken);
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
        $response = $this->getMockBuilder('Guzzle\Http\Message\Response')
            ->disableOriginalConstructor()
            ->getMock();
        $response->expects($this->once())->method('getBody')->will($this->returnValue(file_get_contents(__DIR__.'/../fixtures/data.json')));
        $request = $this->getMock('Guzzle\Http\Message\RequestInterface');
        $request->expects($this->once())->method('send')->will($this->returnValue($response));
        $client = $this->getMock('Guzzle\Http\Client');
        $client->expects($this->once())->method('get')->will($this->returnValue($request));
        Api::setClient($client);
        $api = Api::get('don\'t care about this value');
        $bookmark = "macaron_d_or";
        $this->assertFalse($this->linkResolver->isBookmark($api, $this->link, $bookmark));
    }

    public function testIsBookmarkFound()
    {
        $response = $this->getMockBuilder('Guzzle\Http\Message\Response')
            ->disableOriginalConstructor()
            ->getMock();
        $response->expects($this->once())->method('getBody')->will($this->returnValue(file_get_contents(__DIR__.'/../fixtures/data.json')));
        $request = $this->getMock('Guzzle\Http\Message\RequestInterface');
        $request->expects($this->once())->method('send')->will($this->returnValue($response));
        $client = $this->getMock('Guzzle\Http\Client');
        $client->expects($this->once())->method('get')->will($this->returnValue($request));
        Api::setClient($client);
        $api = Api::get('don\'t care about this value');
        $bookmark = "about";
        $this->assertTrue($this->linkResolver->isBookmark($api, $this->link, $bookmark));
    }

    public function testIsBookmarkDocumentNotFound()
    {
        $response = $this->getMockBuilder('Guzzle\Http\Message\Response')
            ->disableOriginalConstructor()
            ->getMock();
        $response->expects($this->once())->method('getBody')->will($this->returnValue(file_get_contents(__DIR__.'/../fixtures/data.json')));
        $request = $this->getMock('Guzzle\Http\Message\RequestInterface');
        $request->expects($this->once())->method('send')->will($this->returnValue($response));
        $client = $this->getMock('Guzzle\Http\Client');
        $client->expects($this->once())->method('get')->will($this->returnValue($request));
        Api::setClient($client);
        $api = Api::get('don\'t care about this value');
        $bookmark = "macaron_d_or";
        $content = "http://host/doc/Ue0EDd_mqb8Dhk3j";
        $this->assertFalse($this->linkResolver->isBookmarkDocument($api, $this->document, $bookmark));
    }

    public function testIsBookmarkDocument()
    {
        $response = $this->getMockBuilder('Guzzle\Http\Message\Response')
            ->disableOriginalConstructor()
            ->getMock();
        $response->expects($this->once())->method('getBody')->will($this->returnValue(file_get_contents(__DIR__.'/../fixtures/data.json')));
        $request = $this->getMock('Guzzle\Http\Message\RequestInterface');
        $request->expects($this->once())->method('send')->will($this->returnValue($response));
        $client = $this->getMock('Guzzle\Http\Client');
        $client->expects($this->once())->method('get')->will($this->returnValue($request));
        Api::setClient($client);
        $api = Api::get('don\'t care about this value');
        $bookmark = "about";
        $content = "http://host/doc/Ue0EDd_mqb8Dhk3j";
        $this->assertTrue($this->linkResolver->isBookmarkDocument($api, $this->document, $bookmark));
    }

}
