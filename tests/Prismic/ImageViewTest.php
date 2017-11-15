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
        foreach ($document->getStructuredText('product.linked_images')->getImages() as $image) {
            $views[] = $image->getView();
        }
        $this->input = array();
        $this->linkResolver = new FakeLinkResolver();
        foreach ($views as $view) {
            $dom = new DOMDocument;
            $this->input[] = array(
                'view' => $view,
                'parsed' => $dom->loadHTML($view->asHtml($this->linkResolver)),
                'dom' => $dom,
            );
        }
    }

    public function testUnlinkedStartsWithImg()
    {
        foreach ($this->input as $input) {
            if ($input['view']->getLink() !== null && $input['view']->getLink()->getUrl($this->linkResolver) !== null) {
                continue;
            }
            $this->assertRegExp('/^<img\b/', $input['view']->asHtml($this->linkResolver));
        }
    }

    public function testLinkedStartsWithA()
    {
        foreach ($this->input as $input) {
            if ($input['view']->getLink() === null || $input['view']->getLink()->getUrl($this->linkResolver) === null) {
                continue;
            }
            $this->assertRegExp('/^<a\b/', $input['view']->asHtml($this->linkResolver));
        }
    }

    public function testParsable()
    {
        foreach ($this->input as $input) {
            $this->assertNotNull($input['parsed']);
        }
    }

    public function testLinkedHasExactlyOneA()
    {
        $as = array();
        foreach ($this->input as $index => $input) {
            if ($input['view']->getLink() === null || $input['view']->getLink()->getUrl($this->linkResolver) === null) {
                continue;
            }
            $xpath = new DOMXpath($input['dom']);
            $results = $xpath->query('//a');
            $this->assertEquals($results->length, 1);
            $as[$index] = $results->item(0);
        }

        return $as;
    }

    /**
     * @depends testLinkedHasExactlyOneA
     */
    public function testLinkedAHasNoSiblings(array $as)
    {
        foreach ($as as $a) {
            $this->assertNull($a->nextSibling);
            $this->assertNull($a->previousSibling);
        }
    }

    /**
     * @depends testLinkedHasExactlyOneA
     */
    public function testLinkedAAttributes(array $as)
    {
        foreach ($as as $index => $a) {
            $this->assertTrue($a->hasAttribute('href'));
            $this->assertEquals($a->getAttribute('href'), $this->input[$index]['view']->getLink()->getUrl($this->linkResolver));
        }
    }

    public function testExactlyOneImage()
    {
        $imgs = array();
        foreach ($this->input as $index => $input) {
            $xpath = new DOMXpath($input['dom']);
            $results = $xpath->query('//img');
            $this->assertEquals($results->length, 1);
            $imgs[$index] = $results->item(0);
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
            $this->assertEquals($img->getAttribute('src'), $this->input[$index]['view']->getUrl($this->linkResolver));
            $this->assertTrue($img->hasAttribute('width'));
            $this->assertEquals($img->getAttribute('width'), $this->input[$index]['view']->getWidth());
            $this->assertTrue($img->hasAttribute('height'));
            $this->assertEquals($img->getAttribute('height'), $this->input[$index]['view']->getHeight());
            $this->assertTrue($img->hasAttribute('alt'));
            $this->assertEquals($img->getAttribute('alt'), htmlentities($this->input[$index]['view']->getAlt()));
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
