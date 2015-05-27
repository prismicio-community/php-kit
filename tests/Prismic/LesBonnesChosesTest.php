<?php

namespace Prismic\Test;

use Prismic\Api;
use Prismic\Cache\ApcCache;
use Prismic\Response;
use Prismic\Predicates;

class LesBonnesChosesTest extends \PHPUnit_Framework_TestCase
{

    private static $testRepository = 'http://lesbonneschoses.cdn.prismic.io/api';
    private static $previewToken = 'MC5VbDdXQmtuTTB6Z0hNWHF3.c--_vVbvv73vv73vv73vv71EA--_vS_vv73vv70T77-9Ke-_ve-_vWfvv70ebO-_ve-_ve-_vQN377-9ce-_vRfvv70';

    protected function setUp()
    {
        $cache = new ApcCache();
        $cache->clear();
    }

    public function testRetrieveApi()
    {
        $api = Api::get(self::$testRepository);
        $nbRefs = count($api->getData()->getRefs());
        $this->assertEquals($nbRefs, 3);
    }

    /* Tests to calling the API */

    public function testSubmitEverythingForm()
    {
        $api = Api::get(self::$testRepository);
        $masterRef = $api->master()->getRef();
        $response = $results = $api->forms()->everything->ref($masterRef)->submit();
        $this->assertEquals(count($response->getResults()), 20);
        $this->assertEquals($response->getPage(), 1);
        $this->assertEquals($response->getResultsPerPage(), 20);
        $this->assertEquals($response->getResultsSize(), 20);
        $this->assertEquals($response->getTotalResultsSize(), 40);
        $this->assertEquals($response->getTotalPages(), 2);
        $this->assertEquals($response->getNextPage(), "http://lesbonneschoses.cdn.prismic.io/api/documents/search?ref=UlfoxUnM08QWYXdl&page=2&pageSize=20");
        $this->assertEquals($response->getPrevPage(), NULL);
    }

    public function testSubmitEverythingFormWithPredicate()
    {
        $api = Api::get(self::$testRepository);
        $masterRef = $api->master()->getRef();
        $results = $api->forms()->everything->ref($masterRef)->query(Predicates::at('document.type', 'product'))->submit()->getResults();
        $this->assertEquals(count($results), 16);
    }

    public function testParrallelRequests()
    {
        $api = Api::get(self::$testRepository);
        $masterRef = $api->master()->getRef();
        $productsQuery = $api->forms()->everything->ref($masterRef)->query(Predicates::at('document.type', 'product'));
        $storesQuery = $api->forms()->everything->ref($masterRef)->query(Predicates::at('document.type', 'store'));

        $responses = $api->submit($productsQuery, $storesQuery);
        $products = $responses[0]->getResults();
        $stores = $responses[1]->getResults();

        $this->assertEquals(count($products), 16);
        $this->assertEquals(count($stores), 5);
    }

    public function testSubmitProductsForm()
    {
        $api = Api::get(self::$testRepository);
        $masterRef = $api->master()->getRef();
        $results = $api->forms()->products->ref($masterRef)->submit()->getResults();
        $this->assertEquals(16, count($results));
    }

    public function testSubmitProductsFormWithPredicate()
    {
        $api = Api::get(self::$testRepository);
        $masterRef = $api->master()->getRef();
        $results = $api->forms()->products->ref($masterRef)->query(Predicates::at('my.product.flavour', 'Chocolate'))->submit()->getResults();
        $this->assertEquals(5, count($results));
    }

    public function testSubmitProductsFormWithOrderings()
    {
        $api = Api::get(self::$testRepository);
        $masterRef = $api->master()->getRef();
        $results = $api->forms()->products->orderings('[my.product.price]')->ref($masterRef)->submit()->getResults();
        $this->assertEquals('UlfoxUnM0wkXYXbK', $results[0]->getId()); // this is the "Hot Berry Cupcake", the cheapest one.
    }

    public function testRetrieveApiWithPrivilege()
    {
        $api = Api::get(self::$testRepository, self::$previewToken);
        $nbRefs = count($api->getData()->getRefs());
        $this->assertEquals(3, $nbRefs);
    }

    public function testSubmitProductsFormInTheFuture()
    {
        $api = Api::get(self::$testRepository, self::$previewToken);
        $refs = $api->refs();
        $future = $refs['Announcement of new SF shop'];
        $results = $api->forms()->products->ref($future->getRef())->submit()->getResults();
        $this->assertEquals(17, count($results));
    }

    public function testLinkedDocuments()
    {
        $api = Api::get("https://micro.prismic.io/api");
        $masterRef = $api->master()->getRef();
        $results = $api->forms()->everything->ref($masterRef)->query(Predicates::any('document.type', array("doc", "docchapter")))->submit()->getResults();
        $linkedDocuments = $results[0]->getLinkedDocuments();
        $this->assertEquals(1, count($linkedDocuments));
        $this->assertEquals("U0w8OwEAACoAQEvB", $linkedDocuments[0]->getId());
    }

    public function testAfter()
    {
        $api = Api::get(self::$testRepository);
        $masterRef = $api->master()->getRef();
        $results = $api->forms()->products
            ->orderings('[my.product.price]')
            ->after('UlfoxUnM0wkXYXbI')
            ->ref($masterRef)
            ->submit()
            ->getResults();
        $this->assertEquals(count($results), 10);
        $this->assertEquals($results[0]->getId(), "UlfoxUnM0wkXYXbG");
    }

    public function testImmutableObjectCache()
    {
        $api = Api::get(self::$testRepository);
        $masterRef = $api->master()->getRef();
        $results1 = $api->forms()->everything->ref($masterRef)->submit();

        $fakeResponse = new \stdClass;
        $fakeResponse->results = array();
        $fakeResponse->page = 1;
        $fakeResponse->results_per_page = 0;
        $fakeResponse->results_size = 0;
        $fakeResponse->total_results_size = 0;
        $fakeResponse->total_pages = 0;
        $fakeResponse->next_page = NULL;
        $fakeResponse->prev_page = NULL;

        \apc_store('http://lesbonneschoses.cdn.prismic.io/api/documents/search?page=1&pageSize=20&ref=UlfoxUnM08QWYXdl', $fakeResponse, 1000);

        $results2 = $api->forms()->everything->ref($masterRef)->submit();

        $this->assertTrue($results1 != $results2);
    }

    public function testFetchLinks()
    {
        $api = Api::get(self::$testRepository);
        $masterRef = $api->master()->getRef();
        $documents = $api->forms()->everything
            ->ref($masterRef)
            ->fetchLinks('blog-post.author')
            ->query(Predicates::at('document.id', 'UlfoxUnM0wkXYXbt'))
            ->submit()->getResults();
        $link = $documents[0]->getLink('blog-post.relatedpost[0]');
        $this->assertEquals('John M. Martelle, Fine Pastry Magazine', $link->getText('blog-post.author'));
    }

    /* Tests to manipulate the document */
    public function testGetLink()
    {
        $api = Api::get('http://micro.prismic.io/api');
        $masterRef = $api->master()->getRef();
        $documents = $api->forms()->everything
            ->ref($masterRef)
            ->query('[[:d = at(document.id, "UvLDWgEAABoHHn1R")]]')
            ->submit()->getResults();
        $this->assertEquals($documents[0]->getLink('cta.link')->getId(), "U0w8OwEAACoAQEvB");
    }

}
