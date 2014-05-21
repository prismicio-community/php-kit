<?php

namespace Prismic\Test;

use Prismic\Api;
use Prismic\Document;

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
        $results = $api->forms()->everything->query('[[:d = at(document.id, "UssvNAEAAPvPpbr0")]]')->ref($masterRef)->submit();
        $linkExpected = 'https://prismic-io.s3.amazonaws.com/frontwebconf%2F48db2b33-5fd4-4cc5-809d-5ca76342beb4_become+a+sponsor+%28frontwebconf%29.pdf';
        $this->assertEquals($results[0]->get('footerlinks.link')->getUrl(), $linkExpected);
    }

    public function testImageLinksWork()
    {
        $imagelinks = json_decode(file_get_contents(__DIR__.'/../fixtures/imagelinks.json'));
        $document = Document::parse($imagelinks[0]);
        $this->assertEquals($document->get('product.link')->getUrl(), 'https://prismic-io.s3.amazonaws.com/rudysandbox%2F905e76fb-b327-4862-8a31-8f194608dc91_12well_physed-tmagarticle.jpg');
        $this->assertEquals($document->get('product.link')->getWidth(), 592);
    }
}
