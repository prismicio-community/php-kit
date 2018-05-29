<?php
declare(strict_types=1);

namespace Prismic\Test;

use Prismic\Api;
use Prismic\Document;
use Prismic\Document\Fragment;
use Prismic\DocumentInterface;

class DocumentTest extends TestCase
{

    private $api;

    public function setUp()
    {
        parent::setUp();
        $this->api = $this->prophesize(Api::class);
    }

    public function getApi() : Api
    {
        return $this->api->reveal();
    }

    /**
     * @expectedException \Prismic\Exception\InvalidArgumentException
     * @expectedExceptionMessage Failed to decode json payload
     */
    public function testInvalidJsonInFactory()
    {
        Document::fromJsonString('foo', $this->api->reveal());
    }

    public function missingPropertyDataProvider() : array
    {
        return [
            ['{}'],
            ['{"id": "foo"}'],
            ['{"id": "foo", "uid": "some-uid"}'],
            ['{"id": "foo", "uid": "some-uid", "type": "some-type"}'],
            ['{"id": "foo", "uid": "some-uid", "type": "some-type", "tags": []}'],
            ['{"id": "foo", "uid": "some-uid", "type": "some-type", "tags": [], "lang": null}'],
            ['{"id": "foo", "uid": "some-uid", "type": "some-type", "tags": [], "lang": null, "href": "somewhere"}']
        ];
    }

    /**
     * @dataProvider missingPropertyDataProvider
     * @expectedException \Prismic\Exception\InvalidArgumentException
     * @expectedExceptionMessage A required document property was missing from the JSON payload
     */
    public function testRequiredPropertyFailures(string $json)
    {
        Document::fromJsonString($json, $this->api->reveal());
    }

    public function nullPropertyDataProvider() : array
    {
        return [
            ['{"id": null}'],
            ['{"id": "foo", "uid": null, "type": null}'],
            ['{"id": "foo", "uid": null, "type": "some-type", "tags": null}'],
            ['{"id": "foo", "uid": null, "type": "some-type", "tags": [], "lang": null, "href": null}']
        ];
    }

    /**
     * @dataProvider nullPropertyDataProvider
     * @expectedException \Prismic\Exception\InvalidArgumentException
     * @expectedExceptionMessage A required document property was found to be null in the JSON payload
     */
    public function testNonNullPropertyFailures(string $json)
    {
        Document::fromJsonString($json, $this->api->reveal());
    }

    public function getMinimumValidDocument() : Document
    {
        $json = '{
            "id": "ID",
            "uid": "UID",
            "type": "type",
            "tags": [ "tag-1" ],
            "lang": "en-gb",
            "href": "DOCUMENT_URL",
            "slugs": [ "slug-1" ],
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
        }';
        return Document::fromJsonString($json, $this->getApi());
    }

    /**
     * @expectedException \Prismic\Exception\RuntimeException
     * @expectedExceptionMessage Documents cannot be properly hydrated without a Link Resolver being made available to the API
     */
    public function testLinkResolverIsRequiredToHydrateDocuments()
    {
        $this->api->isV1Api()->willReturn(false);
        $this->api->getLinkResolver()->willReturn(null);
        $this->getMinimumValidDocument();
    }

    public function setupMinimumDocument() : Document
    {
        $this->api->isV1Api()->willReturn(false);
        $this->api->getLinkResolver()->willReturn(new FakeLinkResolver());
        return $this->getMinimumValidDocument();
    }

    public function testBasicAccessors()
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
        $this->assertInternalType('array', $doc->getAlternateLanguages());
        $this->assertInstanceOf(\DateTimeInterface::class, $doc->getFirstPublicationDate());
        $this->assertInstanceOf(\DateTimeInterface::class, $doc->getLastPublicationDate());
        $this->assertInstanceOf(Fragment\Text::class, $doc->get('text-value'));
        $this->assertInstanceOf(Fragment\FragmentCollection::class, $doc->getData());
        $this->assertTrue($doc->has('text-value'));
        $this->assertFalse($doc->has('unknown-field'));
    }

    public function testGetTranslation()
    {
        $fakeDoc = $this->prophesize(DocumentInterface::class)->reveal();
        $this->api->getById('TranslatedID')->willReturn($fakeDoc);
        $doc = $this->setupMinimumDocument();
        $this->assertNull($doc->getTranslation('en-us'));
        $this->assertSame($fakeDoc, $doc->getTranslation('en-au'));
    }

    public function testAsLink()
    {
        $doc = $this->setupMinimumDocument();
        $link = $doc->asLink();
        $this->assertInstanceOf(Fragment\Link\DocumentLink::class, $link);
    }
}
