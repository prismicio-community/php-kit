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
            '<h3>Heading 3</h3>' .
            '<h4>Heading 4</h4>' .
            '<h5>Heading 5</h5>' .
            '<h6>Heading 6</h6>' .
            '<p>Paragraph <em>em</em> and <strong>strong</strong></p>' .
            '<p>Paragraph <a href="https://prismic.io">Web link</a> and <a target="_blank" rel="noopener" href="https://prismic-io.s3.amazonaws.com/levi-templeting%2F6acedd00-083a-4335-b789-1ccb64b37ead_success-kid-speak-english.jpg">media link target blank</a></p>' .
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
            '</p>'
        );
        $this->assertEquals($expected, RichText::asHtml($this->richText->description));
        $this->assertEquals($expected, RichText::asHtml($this->richText->description, $this->linkResolver));
    }
}
