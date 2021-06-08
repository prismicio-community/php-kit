<?php
declare(strict_types=1);

namespace Prismic\Test;

use Prismic\ApiData;
use Prismic\Language;
use Prismic\Ref;
use Prismic\Experiments;
use stdClass;

class ApiDataTest extends TestCase
{
    /** @var ApiData */
    private $data;

    public function setUp(): void
    {
        $json = $this->getJsonFixture('data.json');
        $this->data = ApiData::withJsonString($json);
    }

    public function testApiDataCanBeCreatedFromJsonString()
    {
        $json = $this->getJsonFixture('data.json');
        $data = ApiData::withJsonString($json);
        $this->assertInstanceOf(ApiData::class, $data);
    }

    public function testWithJsonStringThrowsExceptionForInvalidJson()
    {
        $this->expectException(\Prismic\Exception\RuntimeException::class);
        $this->expectExceptionMessage("Unable to decode JSON response");
        ApiData::withJsonString('wtf?');
    }

    public function testApiDataHasExpectedValues()
    {
        $this->assertCount(3, $this->data->getRefs());
        $this->assertContainsOnlyInstancesOf(Ref::class, $this->data->getRefs());

        $this->assertCount(3, $this->data->getBookmarks());
        $this->assertContainsOnly('string', $this->data->getBookmarks());

        $this->assertCount(6, $this->data->getTypes());
        $this->assertContainsOnly('string', $this->data->getTypes());

        $this->assertCount(4, $this->data->getTags());
        $this->assertContainsOnly('string', $this->data->getTags());

        $this->assertCount(2, $this->data->getForms());
        $this->assertContainsOnlyInstancesOf(stdClass::class, $this->data->getForms());

        $this->assertCount(4, $this->data->getLanguages());
        $this->assertContainsOnly(Language::class, $this->data->getLanguages());

        $this->assertInstanceOf(Experiments::class, $this->data->getExperiments());

        $this->assertSame('http://lesbonneschoses.prismic.io/auth', $this->data->getOauthInitiate());
        $this->assertSame('http://lesbonneschoses.prismic.io/auth/token', $this->data->getOauthToken());
    }
}
