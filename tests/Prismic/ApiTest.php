<?php
declare(strict_types=1);

namespace Prismic\Test;

use Prismic;
use Prismic\SearchForm;
use Prismic\Api;
use Prismic\ApiData;
use Prismic\Cache\CacheInterface;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\RequestInterface;
use Prophecy\Argument;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;

class ApiTest extends TestCase
{
    /** @var ApiData */
    private $apiData;

    /** @var \GuzzleHttp\ClientInterface */
    private $httpClient;

    /** @var CacheInterface */
    private $cache;

    /**
     * @see fixtures/data.json
     */
    private $expectedMasterRef = 'UgjWQN_mqa8HvPJY';

    public function setUp(): void
    {
        unset($_COOKIE);

        $this->apiData = ApiData::withJsonString($this->getJsonFixture('data.json'));
        $this->httpClient = $this->prophesize(GuzzleClient::class);
        $this->cache = $this->prophesize(CacheInterface::class);
    }

    protected function getApi() : Api
    {
        return Api::get(
            'https://whatever.prismic.io/api/v2',
            'My-Access-Token',
            $this->httpClient->reveal(),
            $this->cache->reveal(),
            99
        );
    }

    protected function getApiWithDefaultData(): Api
    {
        $key = 'https://whatever.prismic.io/api/v2#My-Access-Token';
        $cachedData = serialize($this->apiData);
        $this->cache->get($key)->willReturn($cachedData);
        $this->httpClient->request()->shouldNotBeCalled();

        return $this->getApi();
    }

    public function testCachedApiDataWillBeUsedIfAvailable(): void
    {
        $api = $this->getApiWithDefaultData();
        $this->assertSame(serialize($this->apiData), serialize($api->getData()));
    }

    public function testGetIsCalledOnHttpClientWhenTheCacheIsEmpty(): void
    {
        $key = 'https://whatever.prismic.io/api/v2#My-Access-Token';
        $this->cache->get($key)->willReturn(null);
        $url = 'https://whatever.prismic.io/api/v2?access_token=My-Access-Token';
        $response = $this->prophesize(ResponseInterface::class);
        $response->getBody()->willReturn($this->getJsonFixture('data.json'));
        $this->httpClient->request('GET', $url)->willReturn($response->reveal());

        $this->cache->set(
            Argument::type('string'),
            Argument::type('string'),
            99
        )->shouldBeCalled();

        $api = $this->getApi();
        $this->assertInstanceOf(ClientInterface::class, $api->getHttpClient());
        $this->assertSame(serialize($this->apiData), serialize($api->getData()));
    }

    public function testMasterRefIsReturnedWhenNeitherPreviewOrExperimentsAreActive(): void
    {
        $api = $this->getApiWithDefaultData();
        $this->assertSame($this->expectedMasterRef, $api->ref());
    }

    public function testMasterRefIsReturnedByMasterMethod(): void
    {
        $api = $this->getApiWithDefaultData();
        $ref = $api->master();
        $this->assertInstanceOf(Prismic\Ref::class, $ref);
        $this->assertSame($this->expectedMasterRef, (string) $ref);
    }

    public function testInPreviewAndInExperimentIsFalseWhenNoCookiesAreSet(): void
    {
        $api = $this->getApiWithDefaultData();
        $this->assertFalse($api->inPreview());
        $this->assertFalse($api->inExperiment());
    }

    public function getPreviewRefs(): array
    {
        return [
            [
                [
                    'io.prismic.preview' => 'preview',
                    'other' => 'other',
                ],
                'preview'
            ],
            [
                [
                    'io.prismic.preview' => 'preview',
                    'io.prismic.experiment' => 'experiment',
                ],
                'preview'
            ],
            [
                [
                    'io_prismic_preview' => 'preview',
                ],
                'preview'
            ],
        ];
    }

    /**
     * @dataProvider getPreviewRefs
     */
    public function testPreviewRefIsReturnedWhenPresentInSuperGlobal(array $cookie, string $expect): void
    {
        $_COOKIE = $cookie;
        $api = $this->getApiWithDefaultData();
        $this->assertSame($expect, $api->ref());
    }

    public function testInPreviewIsTrueWhenPreviewCookieIsSet(): void
    {
        $_COOKIE = [
            'io.prismic.preview' => 'whatever',
        ];
        $api = $this->getApiWithDefaultData();
        $this->assertTrue($api->inPreview());
    }

    public function testRefDoesNotReturnStaleExperimentRef(): void
    {
        $_COOKIE = [
            'io.prismic.experiment' => 'Stale Experiment Cookie Value',
        ];
        $api = $this->getApiWithDefaultData();
        $this->assertSame($this->expectedMasterRef, $api->ref());
    }

    public function testCorrectExperimentRefIsReturnedWhenCookieIsSet(): void
    {
        $runningGoogleCookie = '_UQtin7EQAOH5M34RQq6Dg 1';
        $expectedRef = 'VDUUmHIKAZQKk9uq'; // The ref at index 1 for the variations in this experiment
        $_COOKIE = [
            'io.prismic.experiment' => $runningGoogleCookie,
        ];
        $api = $this->getApiWithDefaultData();
        $this->assertSame($expectedRef, $api->ref());
        $this->assertTrue($api->inExperiment());
    }

    /**
     * @depends testCorrectExperimentRefIsReturnedWhenCookieIsSet
     */
    public function testPreviewRefTrumpsExperimentRefWhenSet(): void
    {
        $runningGoogleCookie = '_UQtin7EQAOH5M34RQq6Dg 1';
        $_COOKIE = [
            'io.prismic.experiment' => $runningGoogleCookie,
            'io.prismic.preview'    => 'Preview Ref Cookie Value',
        ];
        $api = $this->getApiWithDefaultData();
        $this->assertTrue($api->inPreview());
        $this->assertFalse($api->inExperiment());
    }

    public function testBookmarkReturnsCorrectDocumentId(): void
    {
        $api = $this->getApiWithDefaultData();
        $this->assertSame('Ue0EDd_mqb8Dhk3j', $api->bookmark('about'));
        $this->assertNull($api->bookmark('unknown-bookmark'));
    }

    public function testFormsReturnsOnlyFormInstances(): void
    {
        $api = $this->getApiWithDefaultData();
        $everything = $api->form('everything');
        $this->assertTrue(isset($everything));
        $this->assertInstanceOf(SearchForm::class, $everything);
    }

    public function testRefsGroupsRefsByLabel(): void
    {
        $api = $this->getApiWithDefaultData();
        $refs = $api->refs();
        $this->assertArrayHasKey('Master', $refs);
        $this->assertArrayHasKey('San Francisco Grand opening', $refs);

        $this->assertContainsOnlyInstancesOf(Prismic\Ref::class, $refs);
    }

    public function testRefsContainsOnlyFirstEncounteredRefWithLabel(): void
    {
        $api = $this->getApiWithDefaultData();
        $refs = $api->refs();
        $this->assertSame('UgjWRd_mqbYHvPJa', (string) $refs['San Francisco Grand opening']);
    }

    public function testGetRefFromLabelReturnsExpectedRef(): void
    {
        $api = $this->getApiWithDefaultData();
        $ref = $api->getRefFromLabel('San Francisco Grand opening');
        $this->assertSame('UgjWRd_mqbYHvPJa', (string) $ref);
    }

    public function testUsefulExceptionIsThrownWhenApiCannotBeReached(): void
    {
        $client = new Client(['connect_timeout' => 0.01]);
        try {
            Api::get('http://example.example', null, $client);
            $this->fail('No exception was thrown');
        } catch (Prismic\Exception\RequestFailureException $e) {
            $this->assertStringContainsString('Api Request Failed', $e->getMessage());
            $this->assertInstanceOf(RequestInterface::class, $e->getRequest());
            $this->assertNull($e->getResponse());
        }
    }
}
