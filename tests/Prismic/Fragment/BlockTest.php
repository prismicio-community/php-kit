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
                $this->arrayToObject([
                    'type' => 'span'
                ])
            ],
            [
                '<span class="class-name">Test with label</span>',
                'Test with label',
                $this->arrayToObject([
                    'type' => 'span',
                    'label' => 'class-name'
                ])
            ],
        ];
    }

    private function arrayToObject(array $values)
    {
        $object = new \stdClass;
        array_walk($values, static fn($value, $index) => $object->$index = $value);
        return $object;
    }


}
