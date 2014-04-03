<?php

namespace Prismic\Test;

use Prismic\Document;
use Prismic\Api;

class DocumentTest extends \PHPUnit_Framework_TestCase
{
    private static $testRepository = 'http://micro.prismic.io/api';

    protected $document;
    protected $micro_api;

    protected function setUp()
    {
        $search = json_decode(file_get_contents(__DIR__.'/../fixtures/search.json'));
        $this->document = Document::parse($search[0]);
        $this->micro_api = Api::get(self::$testRepository);
        $this->linkResolver = new FakeLinkResolver();
    }

    public function testSlug()
    {
        $this->assertEquals($this->document->getSlug(), 'cool-coconut-macaron');
    }

    public function testContainsSlug()
    {
        $this->assertTrue($this->document->containsSlug('coconut-macaron'));
    }

    public function testGetText()
    {
        $this->assertEquals($this->document->getText('product.name'), 'Cool Coconut Macaron');
        $this->assertEquals($this->document->getText('product.short_lede'), 'An island of flavours');
        $this->assertEquals($this->document->getText('product.allergens'), "Fruit\nCats");
        $this->assertEquals($this->document->getText('product.price'), '2.5');
    }

    public function testGetNumber()
    {
        $this->assertEquals($this->document->getNumber('product.price')->getValue(), 2.5);
        $this->assertEquals($this->document->getNumber('product.price', '%s'), '2.5');
    }

    public function testGetBoolean()
    {
        $this->assertTrue($this->document->getBoolean('product.adult'));
        $this->assertTrue($this->document->getBoolean('product.teenager'));
        $this->assertFalse($this->document->getBoolean('product.french'));
        $this->assertTrue($this->document->getBoolean('product.german'));
        $this->assertTrue($this->document->getBoolean('product.spanish'));
        $this->assertFalse($this->document->getBoolean('product.farsi'));
    }

    public function testGetDate()
    {
        $this->assertEquals($this->document->getDate('product.birthdate')->getValue(), '2013-10-23');
        $this->assertEquals($this->document->getDate('product.birthdate', 'Y'), '2013');
    }

    public function testGetImage()
    {
        $this->assertTrue(null != ($this->document->getImage('product.image')));
        $this->assertTrue(null != ($this->document->getImage('product.description')));
    }

    public function testGetAllImages()
    {
        $this->assertEquals(count($this->document->getAllImages('product.description')), 2);
        $this->assertEquals(count($this->document->getAllImages('product.relatedImages')), 2);
    }

    public function testGetImageView()
    {
        $url1 = 'https://prismicio.s3.amazonaws.com/lesbonneschoses/30214ac0c3a51e7516d13c929086c49f49af7988.png';
        $this->assertEquals($this->document->getImageView('product.image', 'main')->getUrl(), $url1);

        $url2 = 'https://prismicio.s3.amazonaws.com/lesbonneschoses/899162db70c73f11b227932b95ce862c63b9df22.jpg';
        $this->assertEquals($this->document->getImageView('product.description', 'main')->getUrl(), $url2);
    }

    public function testGetAllImagesViews()
    {
        $url1 = 'https://prismicio.s3.amazonaws.com/lesbonneschoses/30214ac0c3a51e7516d13c929086c49f49af7988.png';
        $views = $this->document->getAllImageViews('product.image', 'main');
        $view = $views[0];
        $this->assertEquals($view->getUrl(), $url1);

        $url2 = 'https://prismicio.s3.amazonaws.com/lesbonneschoses/899162db70c73f11b227932b95ce862c63b9df22.jpg';
        $views = $this->document->getAllImageViews('product.description', 'main');
        $view = $views[0];
        $this->assertEquals($view->getUrl(), $url2);
    }

    public function testGetStructuredText()
    {
        $this->assertEquals($this->document->getStructuredText('product.name')->asText(), 'Cool Coconut Macaron');
    }

    public function testGetHtml()
    {
        $this->assertEquals($this->document->getHtml('product.name'), '<h1>Cool Coconut Macaron</h1>');
        $this->assertEquals($this->document->getHtml('product.price'), '<span class="number">2.5</span>');
        $this->assertEquals($this->document->getHtml('product.adult'), '<span class="text">yes</span>');
        $this->assertEquals($this->document->getHtml('product.birthdate'), '<time>2013-10-23</time>');
        $this->assertRegExp('`Fruit\s*<br\s*/?>\s*Cats`s', $this->document->getHtml('product.allergens'));
        //TODO
    }

    public function testGetGroup()
    {
        $masterRef = $this->micro_api->master()->getRef();
        $docchapter = $this->micro_api->forms()->everything->ref($masterRef)->query('[[:d = at(document.id, "UrDndQEAALQMyrXF")]]')->submit();
        $docchapter = $docchapter[0];

        $docchapterdocs = $docchapter->getGroup('docchapter.docs')->getArray();
        $this->assertEquals(count($docchapterdocs), 2);
        $this->assertEquals(implode("|", array_keys($docchapterdocs[0])), "linktodoc");
        $this->assertEquals($docchapterdocs[0]['linktodoc']->getType(), 'doc');
        $this->assertEquals($docchapterdocs[0]['linktodoc']->asHtml($this->linkResolver), '<a href="http://host/doc/UrDofwEAALAdpbNH">with-jquery</a>');

        $getSlug = function($doclink) {
            return $doclink['linktodoc']->getSlug();
        };
        $this->assertEquals(implode(' ', array_map($getSlug, $docchapterdocs)), "with-jquery with-bootstrap");

        $this->assertEquals($docchapter->getGroup('docchapter.docs')->asHtml($this->linkResolver), '<section data-field="linktodoc"><a href="http://host/doc/UrDofwEAALAdpbNH">with-jquery</a></section><section data-field="linktodoc"><a href="http://host/doc/UrDp8AEAAPUdpbNL">with-bootstrap</a></section>');
    }

    public function testHasWithExistingField()
    {
        $this->assertEquals($this->document->has('product.name'), true);
    }

    public function testHasWithUnknownField()
    {
        $this->assertEquals($this->document->has('product.badField'), false);
    }
}
