<?php

namespace Prismic\Test;

use Prismic\Api;
use Prismic\Document;
use Prismic\Fragment\Link\WebLink;

class FragmentsTest extends \PHPUnit_Framework_TestCase
{

    private static $testRepository = 'http://frontwebconf.prismic.io/api';

    protected function setUp()
    {
    }

    public function testFileLinksWork()
    {
        $api = Api::get(self::$testRepository);
        $masterRef = $api->master()->getRef();
        $results = $api->forms()->everything->query('[[:d = at(document.id, "UssvNAEAAPvPpbr0")]]')->ref($masterRef)->submit()->getResults();
        $linkExpected = 'https://prismic-io.s3.amazonaws.com/frontwebconf%2F48db2b33-5fd4-4cc5-809d-5ca76342beb4_become+a+sponsor+%28frontwebconf%29.pdf';
        $htmlExpected = '<a href="https://prismic-io.s3.amazonaws.com/frontwebconf%2F48db2b33-5fd4-4cc5-809d-5ca76342beb4_become+a+sponsor+%28frontwebconf%29.pdf">Become a sponsor (FrontWebConf).pdf</a>';
        $this->assertEquals($results[0]->get('footerlinks.link')->getUrl(), $linkExpected);
        $this->assertEquals($results[0]->get('footerlinks.link')->asText(), $linkExpected);
        $this->assertEquals($results[0]->get('footerlinks.link')->asHtml(), $htmlExpected);
    }

    public function testImageLinksWork()
    {
        $imagelinks = json_decode(file_get_contents(__DIR__.'/../fixtures/imagelinks.json'));
        $document = Document::parse($imagelinks[0]);
        $this->assertEquals($document->get('product.link')->getUrl(), 'https://prismic-io.s3.amazonaws.com/rudysandbox%2F905e76fb-b327-4862-8a31-8f194608dc91_12well_physed-tmagarticle.jpg');
        $this->assertEquals($document->get('product.link')->getWidth(), 592);
    }

    public function testWebLinkAsHtml()
    {
        $link = new WebLink("http://prismic.io");
        $this->assertEquals('<a href="http://prismic.io">http://prismic.io</a>', $link->asHtml());
    }

    public function testSlices()
    {
        $response = json_decode(file_get_contents(__DIR__.'/../fixtures/slices.json'));
        $document = Document::parse($response->results[0]);
        $slices = $document->getSliceZone('article.blocks');
        $this->assertEquals($slices->asText(), "C'est un bloc content");
        $this->assertEquals($slices->asHtml(), '<div data-slicetype="features" class="slice"><div class="group-doc"><section data-field="illustration"><img src="https://wroomdev.s3.amazonaws.com/toto/db3775edb44f9818c54baa72bbfc8d3d6394b6ef_hsf_evilsquall.jpg" alt="" width="4285" height="709"></section><section data-field="title"><span class="text">c\'est un bloc features</span></section></div></div><div data-slicetype="text" class="slice"><p>C\'est un bloc content</p></div>');
    }

}
