<?php
declare(strict_types=1);

namespace Prismic\Test;

use DateTimeInterface;
use Prismic\Api;
use Prismic\Document;
use Prismic\Document\Fragment\FragmentCollection;
use Prismic\Document\Fragment\Link\DocumentLink;
use Prismic\Document\Fragment\Text;
use Prismic\DocumentInterface;
use Prismic\Exception\InvalidArgumentException;
use Prismic\Exception\JsonError;
use Prismic\Exception\RuntimeException;
use Prophecy\Prophecy\ObjectProphecy;

class DocumentTest extends TestCase
{
    /** @var Api|ObjectProphecy */
    private $api;

    protected function setUp() : void
    {
        parent::setUp();
        $this->api = $this->prophesize(Api::class);
    }

    public function getApi() : Api
    {
        return $this->api->reveal();
    }

    public function testInvalidJsonInFactory() : void
    {
        $this->expectException(JsonError::class);
        $this->expectExceptionMessage('Failed to decode JSON payload');
        Document::fromJsonString('foo', $this->api->reveal());
    }

    /** @return mixed[] */
    public function missingPropertyDataProvider() : array
    {
        return [
            ['{}'],
            ['{"id": "foo"}'],
            ['{"id": "foo", "uid": "some-uid"}'],
            ['{"id": "foo", "uid": "some-uid", "type": "some-type"}'],
            ['{"id": "foo", "uid": "some-uid", "type": "some-type", "tags": []}'],
            ['{"id": "foo", "uid": "some-uid", "type": "some-type", "tags": [], "lang": null}'],
            ['{"id": "foo", "uid": "some-uid", "type": "some-type", "tags": [], "lang": null, "href": "somewhere"}'],
        ];
    }

    /**
     * @dataProvider missingPropertyDataProvider
     */
    public function testRequiredPropertyFailures(string $json) : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A required document property was missing from the JSON payload');
        Document::fromJsonString($json, $this->api->reveal());
    }

    /** @return mixed[] */
    public function nullPropertyDataProvider() : array
    {
        return [
            'Null ID' => ['{"id": null}'],
            'Null Type' => ['{"id": "foo", "uid": null, "type": null}'],
            'Null Tags' => ['{"id": "foo", "uid": null, "type": "some-type", "tags": null}'],
            'Null Slugs' => ['{"id": "foo", "uid": null, "type": "some-type", "tags": [], "slugs": null}'],
            'Null href' => ['{"id": "foo", "uid": null, "type": "some-type", "tags": [], "slugs": [], "href": null}'],
        ];
    }

    /**
     * @dataProvider nullPropertyDataProvider
     */
    public function testNonNullPropertyFailures(string $json) : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A required document property was found to be null in the JSON payload');
        Document::fromJsonString($json, $this->api->reveal());
    }

    public function getMinimumValidDocument() : Document
    {
        $json = <<<EOF
        {
            "id": "ID",
            "uid": "UID",
            "type": "type",
            "tags": [ "tag-1", "tag-2" ],
            "lang": "en-gb",
            "href": "DOCUMENT_URL",
            "slugs": [ "slug-1", "slug-2" ],
            "first_publication_date": "2018-01-01T12:00:00+0000",
            "last_publication_date": "2018-01-02T12:00:00+0000",
            "alternate_languages": [{
                "id": "TranslatedID",
                "uid": "some-uid.au",
                "type": "doc-type",
                "lang": "en-au"
            }],
            "data": {
                "text-value": "Text Value"
            }
        }
        EOF;

        return Document::fromJsonString($json, $this->getApi());
    }

    public function testLinkResolverIsRequiredToHydrateDocuments() : void
    {
        $this->api->isV1Api()->willReturn(false);
        $this->api->getLinkResolver()->willReturn(null);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Documents cannot be properly hydrated without a Link Resolver being made available to the API');
        $this->getMinimumValidDocument();
    }

    public function setupMinimumDocument() : Document
    {
        $this->api->isV1Api()->willReturn(false);
        $this->api->getLinkResolver()->willReturn(new FakeLinkResolver());

        return $this->getMinimumValidDocument();
    }

    public function testBasicAccessors() : void
    {
        $doc = $this->setupMinimumDocument();
        $this->assertSame('ID', $doc->getId());
        $this->assertSame('UID', $doc->getUid());
        $this->assertSame('type', $doc->getType());
        $this->assertSame('en-gb', $doc->getLang());
        $this->assertSame('DOCUMENT_URL', $doc->getHref());
        $this->assertContains('tag-1', $doc->getTags());
        $this->assertContains('slug-1', $doc->getSlugs());
        $this->assertSame('slug-1', $doc->getSlug());
        $this->assertIsArray($doc->getAlternateLanguages());
        $this->assertInstanceOf(DateTimeInterface::class, $doc->getFirstPublicationDate());
        $this->assertInstanceOf(DateTimeInterface::class, $doc->getLastPublicationDate());
        $this->assertInstanceOf(Text::class, $doc->get('text-value'));
        $this->assertInstanceOf(FragmentCollection::class, $doc->getData());
        $this->assertTrue($doc->has('text-value'));
        $this->assertFalse($doc->has('unknown-field'));
    }

    public function testGetTranslation() : void
    {
        $fakeDoc = $this->prophesize(DocumentInterface::class)->reveal();
        $this->api->getById('TranslatedID')->willReturn($fakeDoc);
        $doc = $this->setupMinimumDocument();
        $this->assertNull($doc->getTranslation('en-us'));
        $this->assertSame($fakeDoc, $doc->getTranslation('en-au'));
    }

    public function testAsLink() : void
    {
        $doc = $this->setupMinimumDocument();
        $link = $doc->asLink();
        $this->assertInstanceOf(DocumentLink::class, $link);
    }
}
