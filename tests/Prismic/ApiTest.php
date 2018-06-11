<?php
declare(strict_types=1);

namespace Prismic\Test;

use Prismic;
use Prismic\SearchForm;
use Prismic\Api;
use Prismic\ApiData;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

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

    /** @var CacheItemPoolInterface */
    private $cache;

    /**
     * @see fixtures/data.json
     */
    private $expectedMasterRef = 'UgjWQN_mqa8HvPJY';

    public function setUp()
    {
        unset($_COOKIE);

        $this->apiData = ApiData::withJsonString($this->getJsonFixture('data.json'));
        $this->httpClient = $this->prophesize(GuzzleClient::class);
        $this->cache = $this->prophesize(CacheItemPoolInterface::class);
    }

    protected function getApi() : Api
    {
        return Api::get(
            'https://whatever.prismic.io/api/v2',
            'My-Access-Token',
            $this->httpClient->reveal(),
            $this->cache->reveal()
        );
    }

    protected function getApiWithDefaultData() : Api
    {
        $url = 'https://whatever.prismic.io/api/v2?access_token=My-Access-Token';
        $key = Api::generateCacheKey($url);
        $item = $this->prophesize(CacheItemInterface::class);
        $item->get()->willReturn($this->apiData);
        $item->isHit()->willReturn(true);
        $this->cache->getItem($key)->willReturn($item->reveal());
        $this->httpClient->request()->shouldNotBeCalled();

        return $this->getApi();
    }

    public function testApiVersionInformation()
    {
        $item = $this->prophesize(CacheItemInterface::class);
        $item->get()->willReturn($this->apiData);
        $item->isHit()->willReturn(true);
        $this->cache->getItem(Argument::type('string'))->willReturn($item->reveal());
        $this->httpClient->request()->shouldNotBeCalled();

        $cache  = $this->cache->reveal();

        $v1Url = 'https://whatever.prismic.io/api';
        $api = Api::get($v1Url, null, null, $cache);
        $this->assertTrue($api->isV1Api());
        $this->assertSame('1.0.0', $api->getApiVersion());

        $v1Url = 'https://whatever.prismic.io/api/v1';
        $api = Api::get($v1Url, null, null, $cache);
        $this->assertTrue($api->isV1Api());
        $this->assertSame('1.0.0', $api->getApiVersion());

        $v2Url = 'https://whatever.prismic.io/api/v2';
        $api = Api::get($v2Url, null, null, $cache);
        $this->assertFalse($api->isV1Api());
        $this->assertSame('2.0.0', $api->getApiVersion());
    }

    public function testCachedApiDataWillBeUsedIfAvailable()
    {
        $api = $this->getApiWithDefaultData();
        $this->assertSame(serialize($this->apiData), serialize($api->getData()));
    }

    public function testGetIsCalledOnHttpClientWhenTheCacheIsEmpty()
    {
        $url = 'https://whatever.prismic.io/api/v2?access_token=My-Access-Token';
        $key = Api::generateCacheKey($url);
        $item = $this->prophesize(CacheItemInterface::class);
        $item->isHit()->willReturn(false);
        $item->set(Argument::any())->shouldBeCalled();
        $this->cache->getItem($key)->willReturn($item->reveal());
        $this->cache->save($item->reveal())->shouldBeCalled();
        $response = $this->prophesize(ResponseInterface::class);
        $response->getBody()->willReturn($this->getJsonFixture('data.json'));
        $this->httpClient->request('GET', $url)->willReturn($response->reveal());


        $api = $this->getApi();
        $this->assertInstanceOf(ClientInterface::class, $api->getHttpClient());
        $this->assertSame(serialize($this->apiData), serialize($api->getData()));
    }

    public function testMasterRefIsReturnedWhenNeitherPreviewOrExperimentsAreActive()
    {
        $api = $this->getApiWithDefaultData();
        $this->assertSame($this->expectedMasterRef, $api->ref());
    }

    public function testMasterRefIsReturnedByMasterMethod()
    {
        $api = $this->getApiWithDefaultData();
        $ref = $api->master();
        $this->assertInstanceOf(Prismic\Ref::class, $ref);
        $this->assertSame($this->expectedMasterRef, (string) $ref);
    }

    public function testInPreviewAndInExperimentIsFalseWhenNoCookiesAreSet()
    {
        $api = $this->getApiWithDefaultData();
        $this->assertFalse($api->inPreview());
        $this->assertFalse($api->inExperiment());
    }

    public function getPreviewRefs()
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
    public function testPreviewRefIsReturnedWhenPresentInSuperGlobal(array $cookie, string $expect)
    {
        $_COOKIE = $cookie;
        $api = $this->getApiWithDefaultData();
        $this->assertSame($expect, $api->ref());
    }

    public function testInPreviewIsTrueWhenPreviewCookieIsSet()
    {
        $_COOKIE = [
            'io.prismic.preview' => 'whatever',
        ];
        $api = $this->getApiWithDefaultData();
        $this->assertTrue($api->inPreview());
    }

    public function testRefDoesNotReturnStaleExperimentRef()
    {
        $_COOKIE = [
            'io.prismic.experiment' => 'Stale Experiment Cookie Value',
        ];
        $api = $this->getApiWithDefaultData();
        $this->assertSame($this->expectedMasterRef, $api->ref());
    }

    public function testCorrectExperimentRefIsReturnedWhenCookieIsSet()
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
    public function testPreviewRefTrumpsExperimentRefWhenSet()
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

    public function testBookmarkReturnsCorrectDocumentId()
    {
        $api = $this->getApiWithDefaultData();
        $this->assertSame('Ue0EDd_mqb8Dhk3j', $api->bookmark('about'));
        $this->assertNull($api->bookmark('unknown-bookmark'));
    }

    public function testFormsReturnsOnlyFormInstances()
    {
        $api = $this->getApiWithDefaultData();
        $forms = $api->forms();
        $this->assertTrue(isset($forms->everything));
        $this->assertInstanceOf(SearchForm::class, $forms->everything);
    }

    public function testRefsGroupsRefsByLabel()
    {
        $api = $this->getApiWithDefaultData();
        $refs = $api->refs();
        $this->assertArrayHasKey('Master', $refs);
        $this->assertArrayHasKey('San Francisco Grand opening', $refs);

        $this->assertContainsOnlyInstancesOf(Prismic\Ref::class, $refs);
    }

    public function testRefsContainsOnlyFirstEncounteredRefWithLabel()
    {
        $api = $this->getApiWithDefaultData();
        $refs = $api->refs();
        $this->assertSame('UgjWRd_mqbYHvPJa', (string) $refs['San Francisco Grand opening']);
    }

    public function testGetRefFromLabelReturnsExpectedRef()
    {
        $api = $this->getApiWithDefaultData();
        $ref = $api->getRefFromLabel('San Francisco Grand opening');
        $this->assertSame('UgjWRd_mqbYHvPJa', (string) $ref);
    }

    public function testUsefulExceptionIsThrownWhenApiCannotBeReached()
    {
        $client = new Client(['connect_timeout' => 0.01]);
        try {
            $api = Api::get('http://example.example', null, $client);
            $this->fail('No exception was thrown');
        } catch (Prismic\Exception\RequestFailureException $e) {
            $this->assertContains('example.example', $e->getMessage());
            $this->assertInstanceOf(RequestInterface::class, $e->getRequest());
            $this->assertNull($e->getResponse());
        }
    }

    /**
     * @expectedException \Prismic\Exception\RequestFailureException
     * @expectedExceptionMessage Api Request Failed
     */
    public function testPreviewSessionWrapsGuzzleExceptions()
    {
        $exception = new \GuzzleHttp\Exception\TransferException('Some Exception Message');
        $this->httpClient->request('GET', 'SomeToken')->willThrow($exception);
        $api = $this->getApiWithDefaultData();
        $api->previewSession('SomeToken', new FakeLinkResolver(), '/');
    }

    public function testDefaultUrlIsReturnedWhenPreviewResponseDoesNotContainMainDocument()
    {
        $response = $this->prophesize(ResponseInterface::class);
        $response->getBody()->willReturn('{}');
        $this->httpClient->request('GET', 'SomeToken')->willReturn($response->reveal());
        $api = $this->getApiWithDefaultData();
        $url = $api->previewSession('SomeToken', new FakeLinkResolver(), '/TheDefaultUrl');
        $this->assertSame('/TheDefaultUrl', $url);
    }

    public function testFirstDocumentUrlIsReturnedWhenAMainDocumentIsSet()
    {
        /**
         * The Preview Response from the API
         */
        $previewResponse = $this->prophesize(ResponseInterface::class);
        $previewResponse->getBody()->willReturn($this->getJsonFixture('preview-session.json'));
        $this->httpClient->request('GET', 'SomeToken')->willReturn($previewResponse->reveal());

        /**
         * Setup the Search Response from the API
         */
        $expectedFormUrl = 'http://repo.prismic.io/api/v2/documents/search?ref=SomeToken&q=%5B%5B%3Ad+%3D+at%28document.id%2C+%22SomeDocumentId%22%29%5D%5D&lang=%2A';
        $formCacheKey = Api::generateCacheKey($expectedFormUrl);
        $searchResult = \json_decode($this->getJsonFixture('search-results.json'));
        $cacheItem = $this->prophesize(CacheItemInterface::class);
        $cacheItem->isHit()->willReturn(true);
        $cacheItem->get()->willReturn($searchResult);
        $this->cache->getItem($formCacheKey)->willReturn($cacheItem->reveal());


        $api = $this->getApiWithDefaultData();
        $api->setLinkResolver(new FakeLinkResolver());
        $url = $api->previewSession('SomeToken', new FakeLinkResolver(), '/TheDefaultUrl');
        $this->assertSame('RESOLVED_LINK', $url);
    }
}
