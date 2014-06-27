<?php

namespace Prismic\Test;

use Prismic\Document;
use Prismic\Api;

class StructuredTextTest extends \PHPUnit_Framework_TestCase
{
    protected $document;

    protected $structuredText;

    protected function setUp()
    {
        $search = json_decode(file_get_contents(__DIR__.'/../fixtures/search.json'));
        $this->document = Document::parse($search[0]);
        $this->structuredText = $this->document->getStructuredText('product.description');
    }

    public function testGetFirstParagraph()
    {
        $content = "If you ever met coconut taste on its bad day, you surely know that coconut, coming from bad-tempered islands, can be rough sometimes.\nThis is after a new line. That is why we like to soften it with a touch of caramel taste in its ganache. The result is the perfect encounter between the finest palm fruit and the most tasty of sugarcane's offspring.";
        $this->assertEquals($content, $this->structuredText->getFirstParagraph()->getText());
    }

    public function testGetParagraphs()
    {
        $this->assertCount(5, $this->structuredText->getParagraphs());
    }

    public function testGetFirstImage()
    {
        $this->assertEquals($this->structuredText->getFirstImage()->getView()->getUrl(), 'https://prismicio.s3.amazonaws.com/lesbonneschoses/899162db70c73f11b227932b95ce862c63b9df22.jpg');
    }

    public function testGetImages()
    {
        $this->assertCount(2, $this->structuredText->getImages());
    }

    public function testGetFirstPreformatted()
    {
        $content = "If you ever met coconut taste on its bad day, you surely know that coconut, coming from bad-tempered islands, can be rough sometimes.\nThis is after a new line. That is why we like to soften it with a touch of caramel taste in its ganache. The result is the perfect encounter between the finest palm fruit and the most tasty of sugarcane's offspring.";
        $this->assertEquals($content, $this->structuredText->getFirstPreformatted()->getText());
    }

    public function testGetPreformatted()
    {
        $this->assertCount(1, $this->structuredText->getPreformatted());
    }

    public function testGetFirstHeading()
    {
        $content = "A heading followed by a preformatted text";
        $this->assertEquals($content, $this->structuredText->getFirstHeading()->getText());
    }

    public function testGetHeadings()
    {
        $this->assertCount(1, $this->structuredText->getHeadings());
    }

    public function testPreformattedBlockFormatRendering()
    {
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

    public function testPreformattedBlockEscapeAndFormatRendering()
    {
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

    public function testPreformattedBlockEscapeRendering()
    {
        $text = "This is <a> test.";
        $bloc = new \Prismic\Fragment\Block\PreformattedBlock($text, array(), null);
        $structuredText = new \Prismic\Fragment\StructuredText(array($bloc));
        $content = "<pre>This is &lt;a&gt; test.</pre>";
        $this->assertEquals($content, $structuredText->asHtml());
    }

    public function testPreformattedBlockHtmlHasNoExtraBreakTags()
    {
        $text = "This pre block has\ntwo lines and a break tag shouldn't be added.";
        $bloc = new \Prismic\Fragment\Block\PreformattedBlock($text, array(), null);
        $structuredText = new \Prismic\Fragment\StructuredText(array($bloc));
        $this->assertRegExp('/block has\ntwo lines/', $structuredText->asHtml());
    }

    public function testDocumentLinkRendering()
    {
        $linkResolver = new FakeLinkResolver();
        $structuredText = $this->document->getStructuredText('product.listLinks');
        $content = '<p><a href="http://host/doc/UjHkUrGIJ7cBlWAb">link1</a></p>';
        $this->assertEquals($content, $structuredText->asHtml($linkResolver));
    }

    public function testNestedDocumentLinkRendering()
    {
        $linkResolver = new FakeLinkResolver();
        $content = '<section data-field="product.listLinks"><p>' .
                   '<a href="http://host/doc/UjHkUrGIJ7cBlWAb">link1</a></p></section>';
        // There should be better way (.*? - (?!)) than this one but no one seems to work.
        $notSection = '([^<]|<[^\/]|<\/[^s]|<\/s[^e]|<\/se[^c]|<\/sec[^t]|<\/sect[^i]|' .
                      '<\/secti[^o]|<\/sectio[^n]|<\/section[^>])';
        $html = preg_replace(
            '/.*(<section data-field="product.listLinks">' . $notSection . '*<\/section>).*/s',
            '$1',
            $this->document->asHtml($linkResolver)
        );
        $this->assertEquals($content, $html);
    }

    public function testDocumentLinkWithLamdaRendering()
    {
        $linkResolver = function ($link) {
            return "http://host/document/".$link->getId();
        };
        $structuredText = $this->document->getStructuredText('product.listLinks');
        $content = '<p><a href="http://host/document/UjHkUrGIJ7cBlWAb">link1</a></p>';
        $this->assertEquals($content, $structuredText->asHtml($linkResolver));
    }

    public function testStructuredTextHtmlHasBreakTags()
    {
        $this->assertRegExp('`can be rough sometimes\.\s*<br\s*/?>\s*This is after a new line\.`s', $this->structuredText->asHtml());
    }

    public function testLinkedImageHasLink()
    {
        $structuredText = $this->document->getStructuredText('product.linked_images');
        $link = $structuredText->getFirstImage()->getView()->getLink();
        $this->assertInstanceOf('\Prismic\Fragment\Link\LinkInterface', $link);
        $linkResolver = new FakeLinkResolver();
        $this->assertEquals($structuredText->asHtml($linkResolver), '<p>Here is some introductory text.</p><p>The following image is linked.</p><p><a href="http://google.com/"><img src="http://fpoimg.com/129x260" alt="" width="260" height="129"></a></p><p><strong>More important stuff</strong></p><p>One more image, this one is not linked:</p><p><img src="http://fpoimg.com/199x300" alt="" width="300" height="199"></p>');
    }

    public function testNonLinkedImageHasNoLink()
    {
        $structuredText = $this->document->getStructuredText('product.linked_images');
        $images = $structuredText->getImages();
        $image = array_pop($images);
        $link = $image->getView()->getLink();
        $this->assertNull($link);
    }

}
