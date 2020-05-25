<?php
declare(strict_types=1);

namespace Prismic\Test;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Psr7\Response;
use Prismic;
use Prismic\Api;
use Prismic\ApiData;
use Prismic\SearchForm;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use function assert;
use function json_decode;
use function serialize;
use function uniqid;
use function urlencode;

class ApiTest extends TestCase
{
    /** @var ApiData */
    private $apiData;

    /** @var ClientInterface|ObjectProphecy */
    private $httpClient;

    /** @var CacheItemPoolInterface|ObjectProphecy */
    private $cache;

    /**
     * @see fixtures/data.json
     *
     * @var string
     */
    private $expectedMasterRef = 'UgjWQN_mqa8HvPJY';

    /** @var string */
    private $repoUrl = 'https://whatever.prismic.io/api/v2';

    protected function setUp() : void
    {
        unset($_COOKIE);

        $this->apiData = ApiData::withJsonString($this->getJsonFixture('data.json'));
        $this->httpClient = $this->prophesize(GuzzleClient::class);
        $this->cache = $this->prophesize(CacheItemPoolInterface::class);
    }

    protected function getApi() : Api
    {
        return Api::get(
            $this->repoUrl,
            'My-Access-Token',
            $this->httpClient->reveal(),
            $this->cache->reveal()
        );
    }

    protected function getApiWithDefaultData() : Api
    {
        $url = $this->repoUrl . '?access_token=My-Access-Token';
        $key = Api::generateCacheKey($url);
        $item = $this->prophesize(CacheItemInterface::class);
        assert($item instanceof CacheItemInterface || $item instanceof ObjectProphecy);
        $item->get()->willReturn($this->apiData);
        $item->isHit()->willReturn(true);
        $this->cache->getItem($key)->willReturn($item->reveal());
        $this->httpClient->request()->shouldNotBeCalled();

        return $this->getApi();
    }

    public function testQueryStringOnApiUrlIsNotDestroyed() : void
    {
        $url = $this->repoUrl . '?someParam=someValue&foo=bar';
        $token = 'My-Access-Token';
        $expect = $url . '&access_token=' . $token;
        $expectedKey = Api::generateCacheKey($expect);

        $item = $this->prophesize(CacheItemInterface::class);
        assert($item instanceof CacheItemInterface || $item instanceof ObjectProphecy);
        $item->get()->willReturn($this->apiData);
        $item->isHit()->willReturn(true);
        $this->cache->getItem($expectedKey)->willReturn($item);

        $api = Api::get($url, $token, null, $this->cache->reveal());

        $this->assertInstanceOf(Api::class, $api);
    }

    public function testApiVersionInformation() : void
    {
        $item = $this->prophesize(CacheItemInterface::class);
        assert($item instanceof CacheItemInterface || $item instanceof ObjectProphecy);
        $item->get()->willReturn($this->apiData);
        $item->isHit()->willReturn(true);
        $this->cache->getItem(Argument::type('string'))->willReturn($item->reveal());
        $this->httpClient->request()->shouldNotBeCalled();

        $cache = $this->cache->reveal();

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

    public function testCachedApiDataWillBeUsedIfAvailable() : void
    {
        $api = $this->getApiWithDefaultData();
        $this->assertSame(serialize($this->apiData), serialize($api->getData()));
    }

    public function testGetIsCalledOnHttpClientWhenTheCacheIsEmpty() : void
    {
        $url = $this->repoUrl . '?access_token=My-Access-Token';
        $key = Api::generateCacheKey($url);
        $item = $this->prophesize(CacheItemInterface::class);
        assert($item instanceof CacheItemInterface || $item instanceof ObjectProphecy);
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

    public function testMasterRefIsReturnedWhenNeitherPreviewOrExperimentsAreActive() : void
    {
        $api = $this->getApiWithDefaultData();
        $this->assertSame($this->expectedMasterRef, $api->ref());
    }

    public function testMasterRefIsReturnedByMasterMethod() : void
    {
        $api = $this->getApiWithDefaultData();
        $ref = $api->master();
        $this->assertInstanceOf(Prismic\Ref::class, $ref);
        $this->assertSame($this->expectedMasterRef, (string) $ref);
    }

    public function testInPreviewAndInExperimentIsFalseWhenNoCookiesAreSet() : void
    {
        $api = $this->getApiWithDefaultData();
        $this->assertFalse($api->inPreview());
        $this->assertFalse($api->inExperiment());
    }

    /** @return mixed[] */
    public function getPreviewRefs() : array
    {
        return [
            [
                [
                    'io.prismic.preview' => 'preview',
                    'other' => 'other',
                ],
                'preview',
            ],
            [
                [
                    'io.prismic.preview' => 'preview',
                    'io.prismic.experiment' => 'experiment',
                ],
                'preview',
            ],
            [
                ['io_prismic_preview' => 'preview'],
                'preview',
            ],
        ];
    }

    /**
     * @param string[] $cookie
     *
     * @dataProvider getPreviewRefs
     */
    public function testPreviewRefIsReturnedWhenPresentInSuperGlobal(array $cookie, string $expect) : void
    {
        $_COOKIE = $cookie;
        $api = $this->getApiWithDefaultData();
        $this->assertSame($expect, $api->ref());
    }

    /**
     * @param string[] $cookie
     *
     * @dataProvider getPreviewRefs
     */
    public function testCookieValuesCanBeSetOverridingSuperGlobal(array $cookie, string $expect) : void
    {
        $_COOKIE = [
            'io.prismic.preview' => uniqid('', true),
        ];
        $api = $this->getApiWithDefaultData();
        $api->setRequestCookies($cookie);
        $this->assertSame($expect, $api->ref());
    }

    public function testInPreviewIsTrueWhenPreviewCookieIsSet() : void
    {
        $_COOKIE = ['io.prismic.preview' => 'whatever'];
        $api = $this->getApiWithDefaultData();
        $this->assertTrue($api->inPreview());
    }

    public function testRefDoesNotReturnStaleExperimentRef() : void
    {
        $_COOKIE = ['io.prismic.experiment' => 'Stale Experiment Cookie Value'];
        $api = $this->getApiWithDefaultData();
        $this->assertSame($this->expectedMasterRef, $api->ref());
    }

    public function testCorrectExperimentRefIsReturnedWhenCookieIsSet() : void
    {
        $runningGoogleCookie = '_UQtin7EQAOH5M34RQq6Dg 1';
        $expectedRef = 'VDUUmHIKAZQKk9uq'; // The ref at index 1 for the variations in this experiment
        $_COOKIE = ['io.prismic.experiment' => $runningGoogleCookie];
        $api = $this->getApiWithDefaultData();
        $this->assertSame($expectedRef, $api->ref());
        $this->assertTrue($api->inExperiment());
    }

    /**
     * @depends testCorrectExperimentRefIsReturnedWhenCookieIsSet
     */
    public function testPreviewRefTrumpsExperimentRefWhenSet() : void
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

    public function testBookmarkReturnsCorrectDocumentId() : void
    {
        $api = $this->getApiWithDefaultData();
        $this->assertSame('Ue0EDd_mqb8Dhk3j', $api->bookmark('about'));
    }

    public function testBookmarkReturnsNullForUnknownBookmark() : void
    {
        $api = $this->getApiWithDefaultData();
        $this->assertNull($api->bookmark('not_known_bookmark_name'));
    }

    public function testFormsCanBeAccessedWithMagicGetter() : void
    {
        $api = $this->getApiWithDefaultData();
        $forms = $api->forms();
        $this->assertTrue(isset($forms->everything));
        $this->assertInstanceOf(SearchForm::class, $forms->everything);
    }

    public function testFormsCanBeRetrievedByNameFromTheCollection() : void
    {
        $api = $this->getApiWithDefaultData();
        $this->assertInstanceOf(SearchForm::class, $api->forms()->getForm('blogs'));
    }

    public function testFormsIsASearchFormCollection() : void
    {
        $api = $this->getApiWithDefaultData();
        $forms = $api->forms();
        $this->assertInstanceOf(Prismic\SearchFormCollection::class, $forms);
    }

    public function testExceptionThrownAccessingASearchFormThatDoesNotExist() : void
    {
        $this->expectException(Prismic\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('The search form named "notHere" does not exist');
        $api = $this->getApiWithDefaultData();
        $api->forms()->notHere;
    }

    public function testRefsGroupsRefsByLabel() : void
    {
        $api = $this->getApiWithDefaultData();
        $refs = $api->refs();
        $this->assertArrayHasKey('Master', $refs);
        $this->assertArrayHasKey('San Francisco Grand opening', $refs);

        $this->assertContainsOnlyInstancesOf(Prismic\Ref::class, $refs);
    }

    public function testRefsContainsOnlyFirstEncounteredRefWithLabel() : void
    {
        $api = $this->getApiWithDefaultData();
        $refs = $api->refs();
        $this->assertSame('UgjWRd_mqbYHvPJa', (string) $refs['San Francisco Grand opening']);
    }

    public function testGetRefFromLabelReturnsExpectedRef() : void
    {
        $api = $this->getApiWithDefaultData();
        $ref = $api->getRefFromLabel('San Francisco Grand opening');
        $this->assertSame('UgjWRd_mqbYHvPJa', (string) $ref);
    }

    public function testUsefulExceptionIsThrownWhenApiCannotBeReached() : void
    {
        $client = new Client(['connect_timeout' => 0.01]);
        try {
            $api = Api::get('http://example.example', null, $client);
            $api->getData();
            $this->fail('No exception was thrown');
        } catch (Prismic\Exception\RequestFailureException $e) {
            $this->assertStringContainsString('example.example', $e->getMessage());
            $this->assertInstanceOf(RequestInterface::class, $e->getRequest());
            $this->assertNull($e->getResponse());
        }
    }

    public function testPreviewSessionWrapsGuzzleExceptions() : void
    {
        $exception = new TransferException('Some Exception Message');
        $this->httpClient->request('GET', $this->repoUrl)->willThrow($exception);
        $api = $this->getApiWithDefaultData();
        $this->expectException(Prismic\Exception\RequestFailureException::class);
        $this->expectExceptionMessage('Api Request Failed');
        $api->previewSession($this->repoUrl, '/');
    }

    public function testExpiredPreviewTokenIsReThrownWithSpecialisedException() : void
    {
        $response = new Response();
        $response->getBody()->write('{"error":"Preview token expired"}');
        $guzzleException = $this->prophesize(RequestException::class);
        $guzzleException->getResponse()->willReturn($response);
        $this->httpClient->request('GET', $this->repoUrl)->willThrow($guzzleException->reveal());
        $api = $this->getApiWithDefaultData();
        $this->expectException(Prismic\Exception\ExpiredPreviewTokenException::class);
        $api->previewSession($this->repoUrl, '/');
    }

    public function testNonUrlLikePreviewTokenIsInvalid() : void
    {
        $token = 'foo';
        $this->httpClient->request('GET', $token)->shouldNotBeCalled();
        $this->expectException(Prismic\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('The preview token "foo" is not a valid url');
        $api = $this->getApiWithDefaultData();
        $api->previewSession($token, '/');
    }

    public function testPreviewUrlIsInvalidForNonMatchingApiHost() : void
    {
        $url = 'https://en-gb.wordpress.org/wordpress-4.9.8-en_GB.zip';
        $token = urlencode($url);
        $this->httpClient->request('GET', $url)->shouldNotBeCalled();
        $this->expectException(Prismic\Exception\InvalidArgumentException::class);
        $this->expectExceptionMessage('The host "en-gb.wordpress.org" does not match the api host "whatever.prismic.io"');
        $api = $this->getApiWithDefaultData();
        $api->previewSession($token, '/');
    }

    public function testDefaultUrlIsReturnedWhenPreviewResponseDoesNotContainMainDocument() : void
    {
        $response = $this->prophesize(ResponseInterface::class);
        $response->getBody()->willReturn('{}');
        $this->httpClient->request('GET', $this->repoUrl)->willReturn($response->reveal());
        $api = $this->getApiWithDefaultData();
        $url = $api->previewSession($this->repoUrl, '/TheDefaultUrl');
        $this->assertSame('/TheDefaultUrl', $url);
    }

    public function testFirstDocumentUrlIsReturnedWhenAMainDocumentIsSet() : void
    {
        /**
         * The Preview Response from the API
         */
        $previewResponse = $this->prophesize(ResponseInterface::class);
        $previewResponse->getBody()->willReturn($this->getJsonFixture('preview-session.json'));
        $this->httpClient->request('GET', $this->repoUrl)->willReturn($previewResponse->reveal());

        /**
         * Setup the Search Response from the API
         */
        $searchResult = json_decode($this->getJsonFixture('search-results.json'));
        $cacheItem = $this->prophesize(CacheItemInterface::class);
        $cacheItem->isHit()->willReturn(true);
        $cacheItem->get()->willReturn($searchResult);
        $this->cache->getItem(Argument::type('string'))->willReturn($cacheItem->reveal());

        $api = $this->getApiWithDefaultData();
        $api->setLinkResolver(new FakeLinkResolver());
        $url = $api->previewSession($this->repoUrl, '/TheDefaultUrl');
        $this->assertSame('RESOLVED_LINK', $url);
    }
}
