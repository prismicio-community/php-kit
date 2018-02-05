<?php

namespace Prismic\Test;

use Prismic\Dom\RichText;
use Prismic\Test\FakeLinkResolver;

class RichTextTest extends \PHPUnit_Framework_TestCase
{
    private $richText;
    private $linkResolver;

    protected function setUp()
    {
        $this->richText = json_decode(file_get_contents(__DIR__.'/../../fixtures/rich-text.json'));
        $this->linkResolver = new FakeLinkResolver();
    }

    public function testAsText()
    {
        $expected = "The title\n";
        $actual = RichText::asText($this->richText->title);
        $this->assertEquals($expected, $actual);
    }

    public function testAsHtml()
    {
        $expected = (
            '<h1>Heading 1</h1>' .
            '<h2>Heading 2</h2>' .
            '<h3 class="the-label">Heading 3</h3>' .
            '<h4>Heading 4</h4>' .
            '<h5>Heading 5</h5>' .
            '<h6>Heading 6</h6>' .
            '<p>Paragraph <em>em</em> and <strong>strong</strong></p>' .
            '<p>Paragraph <a target="_blank" rel="noopener" href="https://prismic.io">Web link</a> and <a href="https://prismic-io.s3.amazonaws.com/levi-templeting%2F6acedd00-083a-4335-b789-1ccb64b37ead_success-kid-speak-english.jpg">media link</a></p>' .
            '<pre>Preformatted block</pre>' .
            '<ul>' .
                '<li><em>Help</em></li>' .
                '<li>Revolver</li>' .
                '<li>Abbey Road</li>' .
            '</ul>' .
            '<ol>' .
                '<li><a href="https://prismic.io">John</a></li>' .
                '<li>Paul</li>' .
                '<li>George</li>' .
                '<li>Ringo</li>' .
            '</ol>' .
            '<p class="block-img">' .
                '<img src="https://prismic-io.s3.amazonaws.com/levi-templeting/357366ce9af5fd05dcd0a76e6ee267fc46c08f6a_mi0003995354.jpg" alt="Alt text">' .
            '</p>' .
            '<div data-oembed="https://www.youtube.com/watch?v=joA7VpZLQaQ" data-oembed-type="video" data-oembed-provider="youtube">' .
                '<iframe width="480" height="270" src="https://www.youtube.com/embed/joA7VpZLQaQ?feature=oembed" frameborder="0" gesture="media" allow="encrypted-media" allowfullscreen></iframe>' .
            '</div>'
        );
        $this->assertEquals($expected, RichText::asHtml($this->richText->description, $this->linkResolver));
        $this->assertEquals($expected, RichText::asHtml($this->richText->description));
    }

    public function testHtmlSerializer()
    {
        $expected = (
            '<h1>Heading 1</h1>' .
            '<h2>Heading 2</h2>' .
            '<div><h3 class="custom the-label">Heading 3</h3></div>' .
            '<h4>Heading 4</h4>' .
            '<h5>Heading 5</h5>' .
            '<h6>Heading 6</h6>' .
            '<p>Paragraph <em>em</em> and <span class="custom">strong</span></p>' .
            '<p>Paragraph <a target="_blank" rel="noopener" href="https://prismic.io">Web link</a> and <a href="https://prismic-io.s3.amazonaws.com/levi-templeting%2F6acedd00-083a-4335-b789-1ccb64b37ead_success-kid-speak-english.jpg">media link</a></p>' .
            '<pre>Preformatted block</pre>' .
            '<ul>' .
                '<li><em>Help</em></li>' .
                '<li>Revolver</li>' .
                '<li>Abbey Road</li>' .
            '</ul>' .
            '<ol>' .
                '<li><a href="https://prismic.io">John</a></li>' .
                '<li>Paul</li>' .
                '<li>George</li>' .
                '<li>Ringo</li>' .
            '</ol>' .
            '<p class="block-img">' .
                '<img src="https://prismic-io.s3.amazonaws.com/levi-templeting/357366ce9af5fd05dcd0a76e6ee267fc46c08f6a_mi0003995354.jpg" alt="Alt text">' .
            '</p>' .
            '<div data-oembed="https://www.youtube.com/watch?v=joA7VpZLQaQ" data-oembed-type="video" data-oembed-provider="youtube">' .
                '<iframe width="480" height="270" src="https://www.youtube.com/embed/joA7VpZLQaQ?feature=oembed" frameborder="0" gesture="media" allow="encrypted-media" allowfullscreen></iframe>' .
            '</div>'
        );
        $htmlSerializer = function($element, $content) {
            if ($element->type === 'heading3') {
                $classes = 'custom';
                if (isset($element->label)) {
                    $classes .= ' ' . $element->label;
                }
                return '<div><h3 class="' . $classes . '">' . $content . '</h3></div>';
            }
            if ($element->type === 'strong') {
                $classes = 'custom';
                if (isset($element->label)) {
                    $classes .= ' ' . $element->label;
                }
                return '<span class="' . $classes . '">' . $content . '</span>';
            }
            return null;
        };
        $actual = RichText::asHtml($this->richText->description, $this->linkResolver, $htmlSerializer);
        $this->assertEquals($expected, $actual);
    }

    public function testNestedSpansAsHtml()
    {
        $expected = (
            '<p>Test <strong><em>lorem</em> ipsum</strong>.</p>' .
            '<p>Test <strong><em>lorem</em> ipsum</strong>.</p>' .
            '<p>Test <strong>lorem <em>ipsum</em></strong>.</p>' .
            '<p>Test <strong>lorem <em>ipsum</em></strong>.</p>' .
            '<p>Test <strong>lor<em>em</em> ipsum</strong>.</p>' .
            '<p>Test <strong>lor<em>em</em> ipsum</strong>.</p>'
        );
        $actual = RichText::asHtml($this->richText->nested_spans);
        $this->assertEquals($expected, $actual);
    }

    public function testLabeledSpanAsHtml()
    {
        $expected = '<p>Paragraph <span class="the-label">labeled span</span>.</p>';
        $actual = RichText::asHtml($this->richText->labeled_span);
        $this->assertEquals($expected, $actual);
    }

    public function testDocumentLink()
    {
        $expected = '<p>This is a <a href="http://host/doc/WKb3BSwAACgAb2M4">document link</a>.</p>';
        $actual = RichText::asHtml($this->richText->document_link, $this->linkResolver);
        $this->assertEquals($expected, $actual);
    }

    public function testDocumentLinkWithoutLinkResolver()
    {
        $expected = '<p>This is a <a href="">document link</a>.</p>';
        $actual = RichText::asHtml($this->richText->document_link);
        $this->assertEquals($expected, $actual);
    }

    public function testBrokenDocumentLink()
    {
        $expected = '<p>This is a <a href="http://host/404">broken document link</a>.</p>';
        $actual = RichText::asHtml($this->richText->broken_document_link, $this->linkResolver);
        $this->assertEquals($expected, $actual);
    }

    public function testBrokenDocumentLinkWithoutLinkResolver()
    {
        $expected = '<p>This is a <a href="">broken document link</a>.</p>';
        $actual = RichText::asHtml($this->richText->broken_document_link);
        $this->assertEquals($expected, $actual);
    }

    public function testEmptyRichText()
    {
        $this->assertEquals('', RichText::asText($this->richText->empty));

        $this->assertEquals('', RichText::asHtml($this->richText->empty, $this->linkResolver));
        $this->assertEquals('', RichText::asHtml($this->richText->empty));
    }
}
