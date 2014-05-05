<?php

namespace Prismic\Test;

use Prismic\Api;

class LesBonnesChosesTest extends \PHPUnit_Framework_TestCase
{

    private static $testRepository = 'http://lesbonneschoses.prismic.io/api';
    private static $previewToken = 'MC5VbDdXQmtuTTB6Z0hNWHF3.c--_vVbvv73vv73vv73vv71EA--_vS_vv73vv70T77-9Ke-_ve-_vWfvv70ebO-_ve-_ve-_vQN377-9ce-_vRfvv70';

    public function testRetrieveApi()
    {
        $api = Api::get(self::$testRepository);
        $nbRefs = count($api->getData()->getRefs());
        $this->assertEquals($nbRefs, 1);
    }

    public function testSubmitEverythingForm()
    {
        $api = Api::get(self::$testRepository);
        $masterRef = $api->master()->getRef();
        $documents = $api->forms()->everything->ref($masterRef)->submit();
        $this->assertEquals(count($documents->getResults()), 20);
        $this->assertEquals($documents->getPage(), 1);
        $this->assertEquals($documents->getResultsPerPage(), 20);
        $this->assertEquals($documents->getResultsSize(), 20);
        $this->assertEquals($documents->getTotalResultsSize(), 40);
        $this->assertEquals($documents->getTotalPages(), 2);
        $this->assertEquals($documents->getNextPage(), 'http://lesbonneschoses.prismic.io/api/documents/search?ref=UkL0hcuvzYUANCrm&page=2&pageSize=20');
        $this->assertEquals($documents->getPrevPage(), '');
    }

    public function testSubmitEverythingFormWithPageAndPageSize()
    {
        $api = Api::get(self::$testRepository);
        $masterRef = $api->master()->getRef();
        $documents = $api->forms()->everything->page(2)->pageSize(10)->ref($masterRef)->submit();
        $this->assertEquals(count($documents->getResults()), 10);
        $this->assertEquals($documents->getPage(), 2);
        $this->assertEquals($documents->getResultsPerPage(), 10);
        $this->assertEquals($documents->getResultsSize(), 10);
        $this->assertEquals($documents->getTotalResultsSize(), 40);
        $this->assertEquals($documents->getTotalPages(), 4);
        $this->assertEquals($documents->getNextPage(), 'http://lesbonneschoses.prismic.io/api/documents/search?ref=UkL0hcuvzYUANCrm&page=3&pageSize=10');
        $this->assertEquals($documents->getPrevPage(), 'http://lesbonneschoses.prismic.io/api/documents/search?ref=UkL0hcuvzYUANCrm&page=1&pageSize=10');
    }

    public function testSubmitEverythingFormWithPredicate()
    {
        $api = Api::get(self::$testRepository);
        $masterRef = $api->master()->getRef();
        $documents = $api->forms()->everything->ref($masterRef)->query('[[:d = at(document.type, "product")]]')->submit();
        $this->assertEquals(count($documents->getResults()), 16);
    }

    public function testSubmitProductsForm()
    {
        $api = Api::get(self::$testRepository);
        $masterRef = $api->master()->getRef();
        $documents = $api->forms()->products->ref($masterRef)->submit();
        $this->assertEquals(count($documents->getResults()), 16);
    }

    public function testSubmitProductsFormWithPredicate()
    {
        $api = Api::get(self::$testRepository);
        $masterRef = $api->master()->getRef();
        $documents = $api->forms()->products->ref($masterRef)->query('[[:d = at(my.product.flavour, "Chocolate")]]')->submit();
        $this->assertEquals(count($documents->getResults()), 5);
    }

    public function testRetrieveApiWithPrivilege()
    {
        $api = Api::get(self::$testRepository, self::$previewToken);
        $nbRefs = count($api->getData()->getRefs());
        $this->assertEquals($nbRefs, 3);
    }

    public function testSubmitProductsFormInTheFuture()
    {
        $api = Api::get(self::$testRepository, self::$previewToken);
        $refs = $api->refs();
        $future = $refs['Announcement of new SF shop'];
        $documents = $api->forms()->products->ref($future->getRef())->submit();
        $this->assertEquals(count($documents->getResults()), 17);
    }
}
