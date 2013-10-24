<?php

namespace Prismic\Test;

use Prismic\Document;

class DocumentTest extends \PHPUnit_Framework_TestCase
{

    protected $document;

    protected function setUp()
    {
        $search = json_decode(file_get_contents(__DIR__.'/../fixtures/search.json'));
        $this->document = Document::parse($search[0]);
    }

    public function testSlug()
    {
        $this->assertEquals($this->document->slug(), 'cool-coconut-macaron');
    }

    public function testContainsSlug()
    {
        $this->assertTrue($this->document->containsSlug('coconut-macaron'));
    }

    public function testGetText()
    {
        $this->assertEquals($this->document->getText('product.name'), 'Cool Coconut Macaron');
        $this->assertEquals($this->document->getText('product.short_lede'), 'An island of flavours');
        $this->assertEquals($this->document->getText('product.allergens'), 'Fruit');
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
        //TODO
    }

    public function testToString()
    {
        $this->assertEquals((string)$this->document, 'UjHYesuvzT0A_yi6');

        $this->document->name = null;
        $this->assertEmpty((string)$this->document->name);
    }

    public function testDynamicGet()
    {
        $this->assertEquals($this->document->name, 'Cool Coconut Macaron');
        $this->assertEquals((string)$this->document->price, 2.5);
        $this->assertEquals((string)$this->document->adult, 'yes');
        $this->assertEquals((string)$this->document->birthdate, '2013-10-23');
        $this->assertTrue(null != (string)$this->document->image);
    }
}
