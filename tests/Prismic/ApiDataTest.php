<?php
declare(strict_types=1);

namespace Prismic\Test;

use Prismic\ApiData;
use Prismic\Exception\JsonError;
use Prismic\Experiments;
use Prismic\Form;
use Prismic\Ref;
use Prismic\Value\Language;
use function array_filter;
use function assert;

class ApiDataTest extends TestCase
{
    /** @var ApiData */
    private $data;

    protected function setUp() : void
    {
        parent::setUp();
        $this->data = ApiData::withJsonString(
            $this->getJsonFixture('data.json')
        );
    }

    public function testWithJsonStringThrowsExceptionForInvalidJson() : void
    {
        $this->expectException(JsonError::class);
        $this->expectExceptionMessage('Failed to decode JSON payload');
        ApiData::withJsonString('wtf?');
    }

    public function testApiDataHasExpectedValues() : void
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
        $this->assertContainsOnlyInstancesOf(Form::class, $this->data->getForms());

        $this->assertInstanceOf(Experiments::class, $this->data->getExperiments());

        $this->assertSame('http://lesbonneschoses.prismic.io/auth', $this->data->getOauthInitiate());
        $this->assertSame('http://lesbonneschoses.prismic.io/auth/token', $this->data->getOauthToken());
    }

    public function testThatLanguagesParsedAreOfTheExpectedType() : void
    {
        $this->assertIsIterable($this->data->languages());
        $this->assertContainsOnlyInstancesOf(Language::class, $this->data->languages());
    }

    public function testThatLanguagesHaveTheExpectedValues() : void
    {
        $languages = $this->data->languages();
        $gb = array_filter((array) $languages, static function (Language $language) : bool {
            return $language->code() === 'en-gb';
        });
        $this->assertCount(1, $gb);
        $gb = $gb[0];
        assert($gb instanceof Language);
        $this->assertEquals('English - Great Britain', $gb->name());
    }
}
