<?php

namespace Prismic\Fragment;

use PHPUnit\Framework\TestCase;
use Prismic\LinkResolver;
use Prismic\Test\FakeLinkResolver;

class BlockTest extends TestCase
{
    public function setUp(): void
    {
        $this->linkResolver = new FakeLinkResolver;
        $this->htmlSerializer = static fn($x) => $x;
    }

    /**
     * @dataProvider dataProvider
     *
     * @param string $result
     * @param string $content
     * @param \stdClass $element
     * @param LinkResolver|null $linkResolver
     * @param \closure|null $htmlSerializer
     * @return void
     */
    public function testRender(
        string $result,
        string $content,
        \stdClass $element,
        LinkResolver $linkResolver = null,
        \closure $htmlSerializer = null
    ): void {
        $block = new Block($element);

        $this->assertSame($result, $block->render($content, $linkResolver, $htmlSerializer));
    }

    public function dataProvider(): array
    {
        return [
            [
                '<span>Test</span>',
                'Test',
                (object)[
                    'type' => 'span'
                ]
            ],
            [
                '<span class="class-name">Test with label</span>',
                'Test with label',
                (object)[
                    'type' => 'span',
                    'label' => 'class-name'
                ]
            ],
            [
                '<h1>Heading 1</h1>',
                'Heading 1',
                (object)[
                    'type' => 'heading1',
                ]
            ],
            [
                '<h2>Heading 2</h2>',
                'Heading 2',
                (object)[
                    'type' => 'heading2',
                ]
            ],
            [
                '<h3>Heading 3</h3>',
                'Heading 3',
                (object)[
                    'type' => 'heading3',
                ]
            ],
            [
                '<h4>Heading 4</h4>',
                'Heading 4',
                (object)[
                    'type' => 'heading4',
                ]
            ],
            [
                '<h5>Heading 5</h5>',
                'Heading 5',
                (object)[
                    'type' => 'heading5',
                ]
            ],
            [
                '<h6>Heading 6</h6>',
                'Heading 6',
                (object)[
                    'type' => 'heading6',
                ]
            ],
            [
                '<span>Heading 7</span>',
                'Heading 7',
                (object)[
                    'type' => 'heading7',
                ]
            ],
            [
                '<p>Paragraph</p>',
                'Paragraph',
                (object)[
                    'type' => 'paragraph',
                ]
            ],
            [
                '<pre>Preformatted</pre>',
                'Preformatted',
                (object)[
                    'type' => 'preformatted',
                ]
            ],
            [
                '<em>Em</em>',
                'Em',
                (object)[
                    'type' => 'em',
                ]
            ],
            [
                '<li>List item</li>',
                'List item',
                (object)[
                    'type' => 'list-item',
                ]
            ],
            [
                '<li>Ordered list item</li>',
                'Ordered list item',
                (object)[
                    'type' => 'o-list-item',
                ]
            ],
        ];
    }

}
