<?php

namespace Prismic\Test;

use Prismic\Api;

class LesBonnesChosesTest extends \PHPUnit_Framework_TestCase
{

    private static $testRepository = 'http://lesbonneschoses.prismic.io/api';
    private static $previewToken = 'MC5VbDdXQmtuTTB6Z0hNWHF3.c--_vVbvv73vv73vv73vv71EA--_vS_vv73vv70T77-9Ke-_ve-_vWfvv70ebO-_ve-_ve-_vQN377-9ce-_vRfvv70';

    protected function setUp()
    {
        $cache = new \Prismic\Cache\DefaultCache();
        $cache->clear();
    }

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
        $results = $api->forms()->everything->ref($masterRef)->submit();
        $this->assertEquals(count($results), 20);
    }

    public function testSubmitEverythingFormWithPredicate()
    {
        $api = Api::get(self::$testRepository);
        $masterRef = $api->master()->getRef();
        $results = $api->forms()->everything->ref($masterRef)->query('[[:d = at(document.type, "product")]]')->submit();
        $this->assertEquals(count($results), 16);
    }

    public function testSubmitProductsForm()
    {
        $api = Api::get(self::$testRepository);
        $masterRef = $api->master()->getRef();
        $results = $api->forms()->products->ref($masterRef)->submit();
        $this->assertEquals(count($results), 16);
    }

    public function testSubmitProductsFormWithPredicate()
    {
        $api = Api::get(self::$testRepository);
        $masterRef = $api->master()->getRef();
        $results = $api->forms()->products->ref($masterRef)->query('[[:d = at(my.product.flavour, "Chocolate")]]')->submit();
        $this->assertEquals(count($results), 5);
    }

    public function testSubmitProductsFormWithOrderings()
    {
        $api = Api::get(self::$testRepository);
        $masterRef = $api->master()->getRef();
        $results = $api->forms()->products->orderings('[my.product.price]')->ref($masterRef)->submit();
        $this->assertEquals($results[0]->getId(), 'UkL0gMuvzYUANCpQ'); // this is the "Hot Berry Cupcake", the cheapest one.
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
        $results = $api->forms()->products->ref($future->getRef())->submit();
        $this->assertEquals(count($results), 17);
    }
}
