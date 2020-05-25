<?php
declare(strict_types=1);

namespace Prismic\Test;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Psr7\Response;
use Prismic\Api;
use Prismic\ApiData;
use Prismic\Document\Hydrator;
use Prismic\DocumentInterface;
use Prismic\Exception\InvalidArgumentException;
use Prismic\Exception\JsonError;
use Prismic\Exception\RequestFailureException;
use Prismic\Exception\RuntimeException;
use Prismic\Form;
use Prismic\Json;
use Prismic\Predicates;
use Prismic\Response as PrismicResponse;
use Prismic\SearchForm;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use stdClass;
use Symfony\Component\Cache\Exception\CacheException;
use Throwable;
use function assert;
use function current;
use function parse_url;
use function sprintf;
use function substr_count;
use const PHP_URL_QUERY;

class SearchFormTest extends TestCase
{
    /** @var ApiData */
    private $apiData;

    /** @var ClientInterface|ObjectProphecy */
    private $httpClient;

    /** @var CacheItemPoolInterface|ObjectProphecy */
    private $cache;

    /** @var Form */
    private $form;

    /** @var Api */
    private $api;

    protected function setUp() : void
    {
        $this->apiData = ApiData::withJsonString($this->getJsonFixture('data.json'));
        $this->form = $this->apiData->getForms()['blogs'];
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

    private function prepareV2SearchResult() : void
    {
        $json = $this->getJsonFixture('search-results.json');
        $this->prepareCacheWithJsonString($json);
    }

    private function prepareV1SearchResult() : void
    {
        $json = $this->getJsonFixture('search-results-v1.json');
        $this->prepareCacheWithJsonString($json);
    }

    private function prepareCacheWithJsonString(string $json) : void
    {
        $cachedJson = Json::decodeObject($json);
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

    public function testGetDataReturnsArray() : void
    {
        $form = $this->getSearchForm();
        $this->assertIsArray($form->getData());
    }

    public function testSetWithAnEmptyKeyThrowsException() : void
    {
        $form = $this->getSearchForm();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Form parameter key must be a non-empty string');
        $form->set('', 'foo');
    }

    public function testSetWithANonScalarValueThrowsException() : void
    {
        $form = $this->getSearchForm();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Form parameter value must be scalar');
        $form->set('page', ['an-array']);
    }

    public function testSetWithAnUnknownKeyThrowsException() : void
    {
        $form = $this->getSearchForm();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown form field parameter');
        $form->set('whatever', 'foo');
    }

    public function testSetStringParamWithNonStringThrowsException() : void
    {
        $form = $this->getSearchForm();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('expects a string parameter');
        $form->set('lang', 1);
    }

    public function testSetIntParamWithNonNumberThrowsException() : void
    {
        $form = $this->getSearchForm();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('expects an integer parameter');
        $form->set('page', 'foo');
    }

    protected function assertSearchFormClone(SearchForm $a, SearchForm $b) : void
    {
        $this->assertNotSame($a, $b);
    }

    public function testSetIsSuccessfulForSingleScalarValue() : void
    {
        $form = $this->getSearchForm();
        $data = $form->getData();
        $this->assertEquals('1', $data['page']);

        $clone = $form->set('page', 10);

        $this->assertSearchFormClone($form, $clone);

        $data = $clone->getData();
        $this->assertEquals('10', $data['page']);
    }

    public function testSetAppendsForMultipleFields() : void
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

    public function testRefAcceptsString() : void
    {
        $form = $this->getSearchForm();
        $clone = $form->ref('some-ref');
        $this->assertSearchFormClone($form, $clone);
        $data = $clone->getData();
        $this->assertSame('some-ref', $data['ref']);
    }

    public function testRefAcceptsRef() : void
    {
        $ref = current($this->apiData->getRefs());
        $form = $this->getSearchForm();
        $clone = $form->ref($ref);
        $data = $clone->getData();
        $this->assertSame((string) $ref, $data['ref']);
    }

    /**
     * @param mixed $expectedValue
     */
    private function assertScalarOptionIsSet(SearchForm $form, string $key, $expectedValue) : void
    {
        $data = $form->getData();
        $this->assertArrayHasKey($key, $data);
        $this->assertSame($expectedValue, $data[$key]);
    }

    private function assertScalarOptionIsNotSet(SearchForm $form, string $key) : void
    {
        $data = $form->getData();
        $this->assertArrayNotHasKey($key, $data);
    }

    public function testAfter() : void
    {
        $this->assertScalarOptionIsSet(
            $this->getSearchForm()->after('Whatever'),
            'after',
            'Whatever'
        );
    }

    public function testLang() : void
    {
        $this->assertScalarOptionIsSet(
            $this->getSearchForm()->lang('en-gb'),
            'lang',
            'en-gb'
        );
    }

    public function testPageSize() : void
    {
        $this->assertScalarOptionIsSet(
            $this->getSearchForm()->pageSize(99),
            'pageSize',
            99
        );
    }

    public function testPage() : void
    {
        $this->assertScalarOptionIsSet(
            $this->getSearchForm()->page(99),
            'page',
            99
        );
    }

    public function testFetchWithStringArgs() : void
    {
        $this->assertScalarOptionIsSet(
            $this->getSearchForm()->fetch('one', 'two', 'three'),
            'fetch',
            'one,two,three'
        );
    }

    public function testFetchWithArrayArg() : void
    {
        $this->assertScalarOptionIsSet(
            $this->getSearchForm()->fetch(...['one', 'two', 'three']),
            'fetch',
            'one,two,three'
        );
    }

    public function testFetchLinksWithStringArgs() : void
    {
        $this->assertScalarOptionIsSet(
            $this->getSearchForm()->fetchLinks('one', 'two', 'three'),
            'fetchLinks',
            'one,two,three'
        );
    }

    public function testOrderingsWithStringArgs() : void
    {
        $this->assertScalarOptionIsSet(
            $this->getSearchForm()->orderings('one', 'two', 'three'),
            'orderings',
            '[one,two,three]'
        );
    }

    public function testOrderingsStripsSquareBrackets() : void
    {
        $this->assertScalarOptionIsSet(
            $this->getSearchForm()->orderings('[my.foo desc]', '[my.bar]'),
            'orderings',
            '[my.foo desc,my.bar]'
        );
    }

    public function testOrderingsWillAcceptUnpackedArrays() : void
    {
        $this->assertScalarOptionIsSet(
            $this->getSearchForm()->orderings(...['[my.a]', 'my.b', 'my.c desc']),
            'orderings',
            '[my.a,my.b,my.c desc]'
        );
    }

    public function testOrderingsFiltersEmptyValues() : void
    {
        $this->assertScalarOptionIsSet(
            $this->getSearchForm()->orderings(...['', 'my.b', '', 'my.c desc']),
            'orderings',
            '[my.b,my.c desc]'
        );
    }

    public function testOrderingsIsNotSetWhenOnlyEmptyValuesAreProvided() : void
    {
        $this->assertScalarOptionIsNotSet(
            $this->getSearchForm()->orderings(...['', '']),
            'orderings'
        );
    }

    public function testStringQueryIsUnprocessedInQuery() : void
    {
        $form = $this->getSearchForm()->query('[:d = at(document.id, "ValidIdentifier")]');
        $data = $form->getData();
        $this->assertArrayHasKey('q', $data);
        $this->assertContains('[:d = at(document.id, "ValidIdentifier")]', $data['q']);
    }

    public function testSinglePredicateArgumentInQuery() : void
    {
        $predicate = Predicates::at('document.id', 'SomeId');
        $expect = sprintf('[%s]', $predicate->q());
        $form = $this->getSearchForm()->query($predicate);
        $data = $form->getData();
        $this->assertContains($expect, $data['q']);
    }

    public function testMultiplePredicatesInQuery() : void
    {
        $predicateA = Predicates::at('document.id', 'SomeId');
        $predicateB = Predicates::any('document.tags', ['Some Tag']);
        $expect = sprintf('[%s%s]', $predicateA->q(), $predicateB->q());
        $form = $this->getSearchForm()->query($predicateA, $predicateB);
        $data = $form->getData();
        $this->assertContains($expect, $data['q']);
    }

    public function testUnpackedPredicateArrayInQuery() : void
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

    public function testRegularArrayArgumentInQuery() : void
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

    public function testEmptyArgumentToQueryHasNoEffect() : void
    {
        $form = $this->getSearchForm()->query('');
        $data = $form->getData();
        $field = $this->form->getField('q');
        $this->assertCount(1, $data['q']);
        $this->assertContains($field->getDefaultValue(), $data['q']);
    }

    public function testUrlRemovesPhpArrayKeys() : void
    {
        $form = $this->getSearchForm()->query('query_string');
        $url = $form->url();
        $query = parse_url($url, PHP_URL_QUERY);
        $this->assertSame(2, substr_count($query, 'q='));
    }

    public function testCachedResponseWillBeReturnedInSubmit() : void
    {
        $this->prepareV2SearchResult();
        $response = $this->getSearchForm()->submit();
        $this->assertInstanceOf(PrismicResponse::class, $response);
    }

    public function testExceptionIsThrownForInvalidForm() : void
    {
        $formJson = '{
            "method": "POST",
            "enctype": "application/x-www-form-urlencoded",
            "action": "https://whatever/api/v2/documents/search",
            "fields": {}
        }';
        $form = Form::withJsonString('foo', $formJson);
        $searchForm = new SearchForm(
            $this->getApiWithDefaultData(),
            $form,
            $form->defaultData()
        );
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Form type not supported');
        $searchForm->submit();
    }

    public function testGuzzleExceptionsAreWrappedInSubmit() : void
    {
        $guzzleException = new TransferException('A Guzzle Exception');
        $this->httpClient->request('GET', Argument::type('string'))->willThrow($guzzleException);
        $item = $this->prophesize(CacheItemInterface::class);
        $item->isHit()->willReturn(false);
        $this->cache->getItem(Argument::type('string'))->willReturn($item->reveal());
        $form = $this->getSearchForm();
        try {
            $form->submit();
            $this->fail('No exception was thrown');
        } catch (RequestFailureException $e) {
            $this->assertSame($guzzleException, $e->getPrevious());
        }
    }

    private function prepareResponse(?string $body = null) : Response
    {
        $body = $body ?: $this->getJsonFixture('search-results.json');
        $response = new Response(
            200,
            ['Cache-Control' => 'max-age=999'],
            $body
        );
        $this->httpClient->request('GET', Argument::type('string'))->willReturn($response);

        return $response;
    }

    public function testResponseInstanceIsReturned() : void
    {
        $this->prepareResponse();
        $item = $this->prophesize(CacheItemInterface::class);
        $item->isHit()->willReturn(false);
        $item->expiresAfter(999)->shouldBeCalled();
        $item->set(Argument::type(stdClass::class))->shouldBeCalled();

        $this->cache->getItem(Argument::type('string'))->willReturn($item->reveal());
        $this->cache->save($item->reveal())->shouldBeCalled();

        $form = $this->getSearchForm();
        $response = $form->submit();
        $this->assertInstanceOf(PrismicResponse::class, $response);
    }

    public function testCountReturnsIntWhenPresentInResponseBody() : void
    {
        $this->prepareResponse();
        $item = $this->prophesize(CacheItemInterface::class);
        $item->isHit()->willReturn(false);
        $item->expiresAfter(999)->shouldBeCalled();
        $item->set(Argument::type(stdClass::class))->shouldBeCalled();

        $this->cache->getItem(Argument::type('string'))->willReturn($item->reveal());
        $this->cache->save($item->reveal())->shouldBeCalled();

        $form = $this->getSearchForm();
        $this->assertIsInt($form->count());
    }

    public function testExceptionIsThrownForInvalidJson() : void
    {
        $this->prepareResponse('Invalid JSON String');
        $item = $this->prophesize(CacheItemInterface::class);
        $item->isHit()->willReturn(false);
        $item->expiresAfter()->shouldNotBeCalled();
        $this->cache->getItem(Argument::type('string'))->willReturn($item->reveal());
        $this->cache->save()->shouldNotBeCalled();
        $form = $this->getSearchForm();
        $this->expectException(JsonError::class);
        $this->expectExceptionMessage('Failed to decode JSON payload');
        $form->submit();
    }

    public function testGetCacheItemWrapsCacheExceptions() : void
    {
        $e = new CacheException();
        $this->cache->getItem(Argument::type('string'))->willThrow($e);
        $form = $this->getSearchForm();
        try {
            $form->submit();
            $this->fail('No exception was thrown');
        } catch (Throwable $exception) {
            $this->assertInstanceOf(RuntimeException::class, $exception);
            $this->assertSame($e, $exception->getPrevious());
        }
    }

    public function testSearchResultContainsDocumentInstances() : void
    {
        $this->prepareV2SearchResult();
        $response = $this->getSearchForm()->submit();
        $this->assertInstanceOf(PrismicResponse::class, $response);
        $results = $response->getResults();
        $this->assertContainsOnlyInstancesOf(DocumentInterface::class, $results);
    }

    public function testSearchResultContainsDocumentInstancesForV1Api() : void
    {
        $this->prepareV1SearchResult();
        $response = $this->getSearchForm(true)->submit();
        $this->assertInstanceOf(PrismicResponse::class, $response);
        $results = $response->getResults();
        $this->assertContainsOnlyInstancesOf(DocumentInterface::class, $results);
    }

    public function testResultsWillBeHydratedWithTheCorrectClass() : void
    {
        $this->prepareV2SearchResult();
        $form = $this->getSearchForm();
        $hydrator = $this->api->getHydrator();
        assert($hydrator instanceof Hydrator);
        $hydrator->mapType('doc-type', Document\CustomDocument::class);
        $response = $form->submit();
        $results = $response->getResults();
        $this->assertContainsOnlyInstancesOf(Document\CustomDocument::class, $results);
    }
}
