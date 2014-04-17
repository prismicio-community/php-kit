<?php

namespace Prismic\Test;

use Prismic\Document;
use DOMDocument;
use DOMXpath;

class ImageViewTest extends \PHPUnit_Framework_TestCase
{

    protected $input;

    protected function setUp()
    {
        $search = json_decode(file_get_contents(__DIR__.'/../fixtures/search.json'));
        $document = Document::parse($search[0]);
        $gallery = $document->get('product.gallery');
        $views = array_values($gallery->getViews());
        $views[] = $gallery->getMain();
        $this->input = array();
        foreach ($views as $view) {
            $dom = new DOMDocument;
            $this->input[] = array(
                'view' => $view,
                'parsed' => $dom->loadHTML($view->asHtml()),
                'dom' => $dom,
            );
        }
    }

    public function testStartsWithImg()
    {
        foreach ($this->input as $input) {
            $this->assertRegExp('/^<img\b/', $input['view']->asHtml());
        }
    }

    public function testParsable()
    {
        foreach ($this->input as $input) {
            $this->assertNotNull($input['parsed']);
        }
    }

    public function testExactlyOneImage()
    {
        $imgs = array();
        foreach ($this->input as $input) {
            $xpath = new DOMXpath($input['dom']);
            $results = $xpath->query('//img');
            $this->assertEquals($results->length, 1);
            $imgs[] = $results->item(0);
        }
        return $imgs;
    }

    /**
     * @depends testExactlyOneImage
     */
    public function testImageHasNoSiblings(array $imgs)
    {
        foreach ($imgs as $img) {
            $this->assertNull($img->nextSibling);
            $this->assertNull($img->previousSibling);
        }
    }

    /**
     * @depends testExactlyOneImage
     */
    public function testAttributes(array $imgs)
    {
        foreach ($imgs as $index => $img) {
            $this->assertTrue($img->hasAttribute('src'));
            $this->assertEquals($img->getAttribute('src'), $this->input[$index]['view']->getUrl());
            $this->assertTrue($img->hasAttribute('width'));
            $this->assertEquals($img->getAttribute('width'), $this->input[$index]['view']->getWidth());
            $this->assertTrue($img->hasAttribute('height'));
            $this->assertEquals($img->getAttribute('height'), $this->input[$index]['view']->getHeight());
            $this->assertTrue($img->hasAttribute('alt'));
            $this->assertEquals($img->getAttribute('alt'), $this->input[$index]['view']->getAlt());
        }
    }

    public function testExtraAttribute()
    {
        $html = $this->input[0]['view']->asHtml(null, array('data-test' => 'attribute value'));
        $dom = new DOMDocument;
        $dom->loadHTML($html);
        $xpath = new DOMXpath($dom);
        $results = $xpath->query('//img');
        $img = $results->item(0);
        $this->assertTrue($img->hasAttribute('data-test'));
        $this->assertEquals($img->getAttribute('data-test'), 'attribute value');
    }

    public function testOverriddenAttribute()
    {
        $html = $this->input[0]['view']->asHtml(null, array('alt' => 'overridden value'));
        $dom = new DOMDocument;
        $dom->loadHTML($html);
        $xpath = new DOMXpath($dom);
        $results = $xpath->query('//img');
        $img = $results->item(0);
        $this->assertEquals($img->getAttribute('alt'), 'overridden value');
    }

}
