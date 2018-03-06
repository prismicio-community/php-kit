<?php
declare(strict_types=1);

namespace Prismic\Test;

use Prismic\Api;
use Prismic\Ref;
use Prismic\SearchForm;
use Prismic\Form;
use Prismic\ApiData;
use Prismic\Cache\CacheInterface;

class SearchFormTest extends TestCase
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

    public function setUp()
    {
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

    protected function getApiWithDefaultData() : Api
    {
        $key = 'https://whatever.prismic.io/api/v2#My-Access-Token';
        $cachedData = serialize($this->apiData);
        $this->cache->get($key)->willReturn($cachedData);
        $this->httpClient->get()->shouldNotBeCalled();

        return $this->getApi();
    }

    protected function getSearchForm() : SearchForm
    {
        return $this->getApiWithDefaultData()
                    ->forms()
                    ->blogs;
    }

    public function testGetDataReturnsArray()
    {
        $form = $this->getSearchForm();
        $this->assertInternalType('array', $form->getData());
    }

    /**
     * @expectedException Prismic\Exception\InvalidArgumentException
     * @expectedExceptionMessage Form parameter key must be a non-empty string
     */
    public function testSetWithAnEmptyKeyThrowsException()
    {
        $form = $this->getSearchForm();
        $form->set('', 'foo');
    }

    /**
     * @expectedException Prismic\Exception\InvalidArgumentException
     * @expectedExceptionMessage Form parameter value must be scalar
     */
    public function testSetWithANonScalarValueThrowsException()
    {
        $form = $this->getSearchForm();
        $form->set('page', ['an-array']);
    }

    /**
     * @expectedException Prismic\Exception\InvalidArgumentException
     * @expectedExceptionMessage Unknown form field parameter
     */
    public function testSetWithAnUnknownKeyThrowsException()
    {
        $form = $this->getSearchForm();
        $form->set('whatever', 'foo');
    }

    /**
     * @expectedException Prismic\Exception\InvalidArgumentException
     * @expectedExceptionMessage expects a string parameter
     */
    public function testSetStringParamWithNonStringThrowsException()
    {
        $form = $this->getSearchForm();
        $form->set('lang', 1);
    }

    /**
     * @expectedException Prismic\Exception\InvalidArgumentException
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
        $api = $this->getApiWithDefaultData();
        $master = $api->master();
        $form = $this->getSearchForm();
        $clone = $form->ref($master);
        $data = $clone->getData();
        $this->assertSame((string) $master, $data['ref']);
    }
}
