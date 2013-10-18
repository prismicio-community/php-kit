<?php

namespace Prismic\Test;

use Prismic\Document;

class StructuredTextTest extends \PHPUnit_Framework_TestCase
{

    protected $document;

    protected function setUp()
    {
        $search = json_decode(file_get_contents(__DIR__.'/../fixtures/search.json'));
        $this->document = Document::parse($search[0]);
        $this->structuredText = $this->document->getStructuredText('product.description');
    }


    public function testGetFirstParagraph() {
        $content = "If you ever met coconut taste on its bad day, you surely know that coconut, coming from bad-tempered islands, can be rough sometimes. That is why we like to soften it with a touch of caramel taste in its ganache. The result is the perfect encounter between the finest palm fruit and the most tasty of sugarcane's offspring.";
        $this->assertEquals($content, $this->structuredText->getFirstParagraph()->text);
    }

    public function testGetFirstImage() {
        $this->assertEquals($this->structuredText->getFirstImage()->view->url, 'https://prismicio.s3.amazonaws.com/lesbonneschoses/899162db70c73f11b227932b95ce862c63b9df22.jpg');
    }

    public function testGetFirstPreformatted() {
        $content = "If you ever met coconut taste on its bad day, you surely know that coconut, coming from bad-tempered islands, can be rough sometimes. That is why we like to soften it with a touch of caramel taste in its ganache. The result is the perfect encounter between the finest palm fruit and the most tasty of sugarcane's offspring.";
        $this->assertEquals($content, $this->structuredText->getFirstPreformatted()->text);
    }

    public function testPreformattedBlockFormatRendering() {
        $text = "This is a test.";
        $spans = array(
            new \Prismic\Fragment\Span\EmSpan(5, 7),
            new \Prismic\Fragment\Span\StrongSpan(8, 9)
        );
        $bloc = new \Prismic\Fragment\Block\PreformattedBlock($text, $spans, null);
        $structuredText = new \Prismic\Fragment\StructuredText(array($bloc));
        $content = "<pre>This <em>is</em> <strong>a</strong> test.</pre>";
        $this->assertEquals($content, $structuredText->asHtml());
    }

    public function testPreformattedBlockEscapeAndFormatRendering() {
        $text = "This is <a> &test.";
        $spans = array(
            new \Prismic\Fragment\Span\EmSpan(5, 7),
            new \Prismic\Fragment\Span\StrongSpan(8, 11)
        );
        $bloc = new \Prismic\Fragment\Block\PreformattedBlock($text, $spans, null);
        $structuredText = new \Prismic\Fragment\StructuredText(array($bloc));
        $content = "<pre>This <em>is</em> <strong>&lt;a&gt;</strong> &amp;test.</pre>";
        $this->assertEquals($content, $structuredText->asHtml());
    }

    public function testPreformattedBlockEscapeRendering() {
        $text = "This is <a> test.";
        $bloc = new \Prismic\Fragment\Block\PreformattedBlock($text, array(), null);
        $structuredText = new \Prismic\Fragment\StructuredText(array($bloc));
        $content = "<pre>This is &lt;a&gt; test.</pre>";
        $this->assertEquals($content, $structuredText->asHtml());
    }

}
