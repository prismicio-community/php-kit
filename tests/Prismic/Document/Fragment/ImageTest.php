<?php
declare(strict_types=1);

namespace Prismic\Test\Document\Fragment;

use Prismic\Document\Fragment\FragmentCollection;
use Prismic\Document\Fragment\Image;
use Prismic\Document\Fragment\Link\WebLink;
use Prismic\Document\Fragment\RichText;
use Prismic\Exception\InvalidArgumentException;
use Prismic\Json;
use Prismic\Test\FakeLinkResolver;
use Prismic\Test\TestCase;
use function assert;

class ImageTest extends TestCase
{
    /** @var FragmentCollection */
    private $collection;

    protected function setUp() : void
    {
        parent::setUp();
        $data = Json::decodeObject($this->getJsonFixture('fragments/image.json'));
        $this->collection = FragmentCollection::factory($data, new FakeLinkResolver());
    }

    public function testFixtureImagesAllReturnImages() : void
    {
        foreach (['single-image-v2', 'multi-image-v2', 'single-image-v1', 'multi-image-v1'] as $key) {
            $image = $this->collection->get($key);
            assert($image instanceof Image);
            $this->assertInstanceOf(Image::class, $image);
        }

        foreach (['richtext-v1', 'richtext-v2'] as $key) {
            $richtext = $this->collection->get($key);
            assert($richtext instanceof RichText);
            $images = $richtext->getImages();
            $this->assertContainsOnlyInstancesOf(Image::class, $images);
        }
    }

    /** @return mixed[] */
    public function invalidImagePayloadProvider() : iterable
    {
        return [
            ['{}'],
            ['{"url": "An URL"}'],
            ['{"url": "An URL", "dimensions": {}}'],
            ['{"url": "An URL", "dimensions": {"width": null}}'],
            ['{"url": "An URL", "dimensions": {"width": 10}}'],
            ['{"url": "An URL", "dimensions": {"width": 10, "height": null}}'],
            ['{"url": "An URL", "dimensions": {"width": 10, "height": "foo"}}'],
        ];
    }

    /**
     * @dataProvider invalidImagePayloadProvider
     */
    public function testInvalidPayloadCases(string $jsonString) : void
    {
        $this->expectException(InvalidArgumentException::class);
        Image::factory(Json::decodeObject($jsonString), new FakeLinkResolver());
    }

    public function testImageFragmentBasics() : void
    {
        $image = $this->collection->get('single-image-v2');
        assert($image instanceof Image);
        $this->assertInstanceOf(Image::class, $image);
        $this->assertIsString($image->getAlt());
        $this->assertIsInt($image->getWidth());
        $this->assertIsInt($image->getHeight());
        $this->assertIsString($image->getUrl());
        $this->assertNull($image->getLink());
        $this->assertFalse($image->hasLink());
        $this->assertNull($image->getLabel());
        $this->assertIsString($image->getCopyright());
        $views = $image->getViews();
        $this->assertIsArray($views);
        $this->assertCount(1, $views);
        $this->assertArrayHasKey('main', $views);
        $this->assertIsFloat($image->ratio());
        $this->assertSame($image->getUrl(), $image->asText());
    }

    public function testHtmlIsCorrectlyRenderedWhenThereIsNoLink() : void
    {
        $image = $this->collection->get('single-image-v2');
        assert($image instanceof Image);
        $html = $image->asHtml();
        $this->assertSame('<img src="IMAGE&#x20;URL" width="960" height="800" alt="ALT&#x20;TEXT" />', $html);
    }

    public function testLabelIsAddedAsCssClassWhenPresent() : void
    {
        $json = '{
            "url" : "URL",
            "dimensions" : {
                "width" : 10,
                "height" : 10
            },
            "label" : "LABEL"
        }';
        $image = Image::factory(Json::decodeObject($json), new FakeLinkResolver());
        $this->assertSame('LABEL', $image->getLabel());
        $this->assertSame('<img src="URL" width="10" height="10" alt="" class="LABEL" />', $image->asHtml());
    }

    public function testLinkIsRetrievableWhenPresentInJsonPayload() : void
    {
        $json = '{
            "type": "image",
            "url": "URL",
            "dimensions": {
                "width": 10,
                "height": 10
            },
            "linkTo": {
                "link_type": "Web",
                "url": "URL"
            }
        }';
        $image = Image::factory(Json::decodeObject($json), new FakeLinkResolver());
        assert($image instanceof Image);
        $this->assertTrue($image->hasLink());
        $this->assertInstanceOf(WebLink::class, $image->getLink());
        $expect = '<a href="URL"><img src="URL" width="10" height="10" alt="" /></a>';
        $this->assertSame($expect, $image->asHtml());
    }
}
