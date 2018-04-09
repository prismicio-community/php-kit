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

    /** @var Form */
    private $form;

    /**
     * @see fixtures/data.json
     */
    private $expectedMasterRef = 'UgjWQN_mqa8HvPJY';

    public function setUp()
    {
        $this->apiData = ApiData::withJsonString($this->getJsonFixture('data.json'));
        $this->form = Form::withJsonObject($this->apiData->getForms()['blogs']);
        $this->httpClient = $this->prophesize(GuzzleClient::class);
        $this->cache = $this->prophesize(CacheInterface::class);
    }

    protected function getSearchForm() : SearchForm
    {
        return new SearchForm(
            $this->httpClient->reveal(),
            $this->cache->reveal(),
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
            $this->getSearchForm()->fetch('one','two','three'),
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
            $this->getSearchForm()->fetchLinks('one','two','three'),
            'fetchLinks',
            'one,two,three'
        );
    }

    public function testOrderingsWithStringArgs()
    {
        $this->assertScalarOptionIsSet(
            $this->getSearchForm()->orderings('one','two','three'),
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

}
