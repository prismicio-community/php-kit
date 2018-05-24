<?php
declare(strict_types=1);

namespace Prismic\Test\Document\Fragment;

use Prismic\Document\Fragment\Embed;
use Prismic\Test\FakeLinkResolver;
use Prismic\Test\TestCase;

class EmbedTest extends TestCase
{
    /**
     * @expectedException \Prismic\Exception\InvalidArgumentException
     * @expectedExceptionMessage The type and embed_url properties are required elements of the JSON payload
     */
    public function testExceptionThrownWithNoEmbedUrl()
    {
        Embed::factory(\json_decode('{}'), new FakeLinkResolver());
    }

    public function testExpectedValues()
    {
        $data = \json_decode($this->getJsonFixture('fragments/embed.json'));
        /** @var Embed $embed */
        $embed = Embed::factory($data, new FakeLinkResolver());
        $this->assertSame('YouTube', $embed->getProvider());
        $this->assertSame('video', $embed->getType());
        $this->assertSame('EMBED_URL', $embed->getUrl());
        $this->assertSame('EMBED_URL', $embed->asText());
        $this->assertSame('EMBED_HTML_STRING', $embed->getHtml());
        $this->assertSame(500, $embed->getWidth());
        $this->assertSame(500, $embed->getHeight());
        $this->assertInternalType('string', $embed->asHtml());
    }
}
