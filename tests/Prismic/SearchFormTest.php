<?php
declare(strict_types=1);

namespace Prismic\Test;

use Prismic\Api;
use Prismic\Document\Hydrator;
use Prismic\DocumentInterface;
use Prismic\Exception\RuntimeException;
use Prismic\Ref;
use Prismic\SearchForm;
use Prismic\Form;
use Prismic\ApiData;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Prismic\Predicates;
use GuzzleHttp\Psr7\Response;
use Prismic\Response as PrismicResponse;
use Prophecy\Argument;
use Symfony\Component\Cache\Exception\CacheException;

class SearchFormTest extends TestCase
{

    /** @var ApiData */
    private $apiData;

    /** @var \GuzzleHttp\ClientInterface */
    private $httpClient;

    /** @var CacheItemPoolInterface */
    private $cache;

    /** @var Form */
    private $form;

    /** @var Api */
    private $api;

    /**
     * @see fixtures/data.json
     */
    private $expectedMasterRef = 'UgjWQN_mqa8HvPJY';

    public function setUp()
    {
        $this->apiData = ApiData::withJsonString($this->getJsonFixture('data.json'));
        $this->form = Form::withJsonObject($this->apiData->getForms()['blogs']);
        $this->httpClient = $this->prophesize(GuzzleClient::class);
        $this->cache = $this->prophesize(CacheItemPoolInterface::class);
    }

    protected function getApi(bool $v1 = false) : Api
    {
        $version = $v1 ? 'v1' : 'v2';
        $api = Api::get(
            'https://whatever.prismic.io/api/' . $version,
            'My-Access-Token',
            $this->httpClient->reveal(),
            $this->cache->reveal()
        );
        $api->setLinkResolver(new FakeLinkResolver());
        $this->api = $api;
        return $api;
    }

    protected function getApiWithDefaultData(bool $v1 = false) : Api
    {
        $version = $v1 ? 'v1' : 'v2';
        $url = 'https://whatever.prismic.io/api/' . $version . '?access_token=My-Access-Token';
        $key = Api::generateCacheKey($url);
        $item = $this->prophesize(CacheItemInterface::class);
        $item->get()->willReturn($this->apiData);
        $item->isHit()->willReturn(true);
        $this->cache->getItem($key)->willReturn($item->reveal());
        $this->httpClient->request()->shouldNotBeCalled();

        return $this->getApi($v1);
    }

    private function prepareV2SearchResult()
    {
        $json = $this->getJsonFixture('search-results.json');
        $this->prepareCacheWithJsonString($json);
    }

    private function prepareV1SearchResult()
    {
        $json = $this->getJsonFixture('search-results-v1.json');
        $this->prepareCacheWithJsonString($json);
    }

    private function prepareCacheWithJsonString(string $json)
    {
        $cachedJson = \json_decode($json);
        $cacheItem = $this->prophesize(CacheItemInterface::class);
        $cacheItem->get()->willReturn($cachedJson);
        $cacheItem->isHit()->willReturn(true);
        $this->cache->getItem(Argument::type('string'))->willReturn($cacheItem);
    }

    protected function getSearchForm(bool $v1 = false) : SearchForm
    {
        return new SearchForm(
            $this->getApiWithDefaultData($v1),
            $this->form,
            $this->form->defaultData()
        );
    }

    public function testGetDataReturnsArray()
    {
        $form = $this->getSearchForm();
        $this->assertInternalType('array', $form->getData());
    }

    /**
     * @expectedException \Prismic\Exception\InvalidArgumentException
     * @expectedExceptionMessage Form parameter key must be a non-empty string
     */
    public function testSetWithAnEmptyKeyThrowsException()
    {
        $form = $this->getSearchForm();
        $form->set('', 'foo');
    }

    /**
     * @expectedException \Prismic\Exception\InvalidArgumentException
     * @expectedExceptionMessage Form parameter value must be scalar
     */
    public function testSetWithANonScalarValueThrowsException()
    {
        $form = $this->getSearchForm();
        $form->set('page', ['an-array']);
    }

    /**
     * @expectedException \Prismic\Exception\InvalidArgumentException
     * @expectedExceptionMessage Unknown form field parameter
     */
    public function testSetWithAnUnknownKeyThrowsException()
    {
        $form = $this->getSearchForm();
        $form->set('whatever', 'foo');
    }

    /**
     * @expectedException \Prismic\Exception\InvalidArgumentException
     * @expectedExceptionMessage expects a string parameter
     */
    public function testSetStringParamWithNonStringThrowsException()
    {
        $form = $this->getSearchForm();
        $form->set('lang', 1);
    }

    /**
     * @expectedException \Prismic\Exception\InvalidArgumentException
     * @expectedExceptionMessage expects an integer parameter
     */
    public function testSetIntParamWithNonNumberThrowsException()
    {
        $form = $this->getSearchForm();
        $form->set('page', 'foo');
    }

    protected function assertSearchFormClone(SearchForm $a, SearchForm $b)
    {
        $this->assertNotSame($a, $b);
    }

    public function testSetIsSuccessfulForSingleScalarValue()
    {
        $form = $this->getSearchForm();
        $data = $form->getData();
        $this->assertEquals('1', $data['page']);

        $clone = $form->set('page', 10);

        $this->assertSearchFormClone($form, $clone);

        $data = $clone->getData();
        $this->assertEquals('10', $data['page']);
    }

    public function testSetAppendsForMultipleFields()
    {
        $form = $this->getSearchForm();
        $data = $form->getData();
        $this->assertCount(1, $data['q']);
        $this->assertNotContains('some-value', $data['q']);
        $clone = $form->set('q', 'some-value');
        $data = $clone->getData();
        $this->assertCount(2, $data['q']);
        $this->assertContains('some-value', $data['q']);
    }

    public function testRefAcceptsString()
    {
        $form = $this->getSearchForm();
        $clone = $form->ref('some-ref');
        $this->assertSearchFormClone($form, $clone);
        $data = $clone->getData();
        $this->assertSame('some-ref', $data['ref']);
    }

    public function testRefAcceptsRef()
    {
        $ref = current($this->apiData->getRefs());
        $form = $this->getSearchForm();
        $clone = $form->ref($ref);
        $data = $clone->getData();
        $this->assertSame((string) $ref, $data['ref']);
    }

    private function assertScalarOptionIsSet(SearchForm $form, string $key, $expectedValue)
    {
        $data = $form->getData();
        $this->assertArrayHasKey($key, $data);
        $this->assertSame($expectedValue, $data[$key]);
    }

    private function assertScalarOptionIsNotSet(SearchForm $form, string $key)
    {
        $data = $form->getData();
        $this->assertArrayNotHasKey($key, $data);
    }

    public function testAfter()
    {
        $this->assertScalarOptionIsSet(
            $this->getSearchForm()->after('Whatever'),
            'after',
            'Whatever'
        );
    }

    public function testLang()
    {
        $this->assertScalarOptionIsSet(
            $this->getSearchForm()->lang('en-gb'),
            'lang',
            'en-gb'
        );
    }

    public function testPageSize()
    {
        $this->assertScalarOptionIsSet(
            $this->getSearchForm()->pageSize(99),
            'pageSize',
            99
        );
    }

    public function testPage()
    {
        $this->assertScalarOptionIsSet(
            $this->getSearchForm()->page(99),
            'page',
            99
        );
    }

    public function testFetchWithStringArgs()
    {
        $this->assertScalarOptionIsSet(
            $this->getSearchForm()->fetch('one', 'two', 'three'),
            'fetch',
            'one,two,three'
        );
    }

    public function testFetchWithArrayArg()
    {
        $this->assertScalarOptionIsSet(
            $this->getSearchForm()->fetch(...['one','two','three']),
            'fetch',
            'one,two,three'
        );
    }

    public function testFetchLinksWithStringArgs()
    {
        $this->assertScalarOptionIsSet(
            $this->getSearchForm()->fetchLinks('one', 'two', 'three'),
            'fetchLinks',
            'one,two,three'
        );
    }

    public function testOrderingsWithStringArgs()
    {
        $this->assertScalarOptionIsSet(
            $this->getSearchForm()->orderings('one', 'two', 'three'),
            'orderings',
            '[one,two,three]'
        );
    }

    public function testOrderingsStripsSquareBrackets()
    {
        $this->assertScalarOptionIsSet(
            $this->getSearchForm()->orderings('[my.foo desc]', '[my.bar]'),
            'orderings',
            '[my.foo desc,my.bar]'
        );
    }

    public function testOrderingsWillAcceptUnpackedArrays()
    {
        $this->assertScalarOptionIsSet(
            $this->getSearchForm()->orderings(...['[my.a]', 'my.b', 'my.c desc']),
            'orderings',
            '[my.a,my.b,my.c desc]'
        );
    }

    public function testOrderingsFiltersEmptyValues()
    {
        $this->assertScalarOptionIsSet(
            $this->getSearchForm()->orderings(...['', 'my.b', '', 'my.c desc']),
            'orderings',
            '[my.b,my.c desc]'
        );
    }

    public function testOrderingsIsNotSetWhenOnlyEmptyValuesAreProvided()
    {
        $this->assertScalarOptionIsNotSet(
            $this->getSearchForm()->orderings(...['', '']),
            'orderings'
        );
    }

    public function testStringQueryIsUnprocessedInQuery()
    {
        $form = $this->getSearchForm()->query('[:d = at(document.id, "ValidIdentifier")]');
        $data = $form->getData();
        $this->assertArrayHasKey('q', $data);
        $this->assertContains('[:d = at(document.id, "ValidIdentifier")]', $data['q']);
    }

    public function testSinglePredicateArgumentInQuery()
    {
        $predicate = Predicates::at('document.id', 'SomeId');
        $expect = sprintf('[%s]', $predicate->q());
        $form = $this->getSearchForm()->query($predicate);
        $data = $form->getData();
        $this->assertContains($expect, $data['q']);
    }

    public function testMultiplePredicatesInQuery()
    {
        $predicateA = Predicates::at('document.id', 'SomeId');
        $predicateB = Predicates::any('document.tags', ['Some Tag']);
        $expect = sprintf('[%s%s]', $predicateA->q(), $predicateB->q());
        $form = $this->getSearchForm()->query($predicateA, $predicateB);
        $data = $form->getData();
        $this->assertContains($expect, $data['q']);
    }

    public function testUnpackedPredicateArrayInQuery()
    {
        $query = [
            Predicates::at('document.id', 'SomeId'),
            Predicates::any('document.tags', ['Some Tag']),
        ];
        $expect = sprintf('[%s%s]', $query[0]->q(), $query[1]->q());
        $form = $this->getSearchForm()->query(...$query);
        $data = $form->getData();
        $this->assertContains($expect, $data['q']);
    }

    public function testRegularArrayArgumentInQuery()
    {
        $query = [
            Predicates::at('document.id', 'SomeId'),
            Predicates::any('document.tags', ['Some Tag']),
        ];
        $expect = sprintf('[%s%s]', $query[0]->q(), $query[1]->q());
        $form = $this->getSearchForm()->query($query);
        $data = $form->getData();
        $this->assertContains($expect, $data['q']);
    }

    public function testEmptyArgumentToQueryHasNoEffect()
    {
        $form = $this->getSearchForm()->query('');
        $data = $form->getData();
        $field = $this->form->getField('q');
        $this->assertCount(1, $data['q']);
        $this->assertContains($field->getDefaultValue(), $data['q']);
    }

    public function testUrlRemovesPhpArrayKeys()
    {
        $form = $this->getSearchForm()->query('query_string');
        $url = $form->url();
        $query = parse_url($url, PHP_URL_QUERY);
        $this->assertSame(2, substr_count($query, 'q='));
    }

    public function testCachedResponseWillBeReturnedInSubmit()
    {
        $this->prepareV2SearchResult();
        $response = $this->getSearchForm()->submit();
        $this->assertInstanceOf(PrismicResponse::class, $response);
    }

    /**
     * @expectedException \Prismic\Exception\RuntimeException
     * @expectedExceptionMessage Form type not supported
     */
    public function testExceptionIsThrownForInvalidForm()
    {
        $formJson = '{
            "method": "POST",
            "enctype": "application/x-www-form-urlencoded",
            "action": "https://whatever/api/v2/documents/search",
            "fields": {}
        }';
        $form = Form::withJsonString($formJson);
        $searchForm = new SearchForm(
            $this->getApiWithDefaultData(),
            $form,
            $form->defaultData()
        );
        $searchForm->submit();
    }

    public function testGuzzleExceptionsAreWrappedInSubmit()
    {
        $guzzleException = new \GuzzleHttp\Exception\TransferException('A Guzzle Exception');
        /** @var \Prophecy\Prophecy\ObjectProphecy $this->httpClient */
        $this->httpClient->request('GET', Argument::type('string'))->willThrow($guzzleException);
        $item = $this->prophesize(CacheItemInterface::class);
        $item->isHit()->willReturn(false);
        $this->cache->getItem(Argument::type('string'))->willReturn($item->reveal());
        $form = $this->getSearchForm();
        try {
            $form->submit();
            $this->fail('No exception was thrown');
        } catch (\Prismic\Exception\RequestFailureException $e) {
            $this->assertSame($guzzleException, $e->getPrevious());
        }
    }

    private function prepareResponse(?string $body = null) : Response
    {
        $body = $body ? $body : $this->getJsonFixture('search-results.json');
        $response = new Response(
            200,
            ['Cache-Control' => 'max-age=999'],
            $body
        );
        $this->httpClient->request('GET', Argument::type('string'))->willReturn($response);
        return $response;
    }

    public function testResponseInstanceIsReturned()
    {
        $this->prepareResponse();
        $item = $this->prophesize(CacheItemInterface::class);
        $item->isHit()->willReturn(false);
        $item->expiresAfter(999)->shouldBeCalled();
        $item->set(Argument::type(\stdClass::class))->shouldBeCalled();

        $this->cache->getItem(Argument::type('string'))->willReturn($item->reveal());
        $this->cache->save($item->reveal())->shouldBeCalled();

        $form = $this->getSearchForm();
        $response = $form->submit();
        $this->assertInstanceOf(PrismicResponse::class, $response);
    }

    public function testCountReturnsIntWhenPresentInResponseBody()
    {
        $this->prepareResponse();
        $item = $this->prophesize(CacheItemInterface::class);
        $item->isHit()->willReturn(false);
        $item->expiresAfter(999)->shouldBeCalled();
        $item->set(Argument::type(\stdClass::class))->shouldBeCalled();

        $this->cache->getItem(Argument::type('string'))->willReturn($item->reveal());
        $this->cache->save($item->reveal())->shouldBeCalled();

        $form = $this->getSearchForm();
        $this->assertInternalType('integer', $form->count());
    }

        /**
     * @expectedException \Prismic\Exception\RuntimeException
     * @expectedExceptionMessage Unable to decode json response
     */
    public function testExceptionIsThrownForInvalidJson()
    {
        $this->prepareResponse('Invalid JSON String');
        $item = $this->prophesize(CacheItemInterface::class);
        $item->isHit()->willReturn(false);
        $item->expiresAfter()->shouldNotBeCalled();
        $this->cache->getItem(Argument::type('string'))->willReturn($item->reveal());
        $this->cache->save()->shouldNotBeCalled();
        $form = $this->getSearchForm();
        $form->submit();
    }

    public function testGetCacheItemWrapsCacheExceptions()
    {
        $e = new CacheException();
        $this->cache->getItem(Argument::type('string'))->willThrow($e);
        $form = $this->getSearchForm();
        try {
            $form->submit();
            $this->fail('No exception was thrown');
        } catch (\Exception $exception) {
            $this->assertInstanceOf(RuntimeException::class, $exception);
            $this->assertSame($e, $exception->getPrevious());
        }
    }

    public function testSearchResultContainsDocumentInstances()
    {
        $this->prepareV2SearchResult();
        $response = $this->getSearchForm()->submit();
        $this->assertInstanceOf(PrismicResponse::class, $response);
        $results = $response->getResults();
        $this->assertContainsOnlyInstancesOf(DocumentInterface::class, $results);
    }

    public function testSearchResultContainsDocumentInstancesForV1Api()
    {
        $this->prepareV1SearchResult();
        $response = $this->getSearchForm(true)->submit();
        $this->assertInstanceOf(PrismicResponse::class, $response);
        $results = $response->getResults();
        $this->assertContainsOnlyInstancesOf(DocumentInterface::class, $results);
    }

    public function testResultsWillBeHydratedWithTheCorrectClass()
    {
        $this->prepareV2SearchResult();
        $form = $this->getSearchForm();
        /** @var Hydrator $hydrator */
        $hydrator = $this->api->getHydrator();
        $hydrator->mapType('doc-type', Document\CustomDocument::class);
        $response = $form->submit();
        $results = $response->getResults();
        $this->assertContainsOnlyInstancesOf(Document\CustomDocument::class, $results);
    }
}
