<?php

namespace Prismic\Test;

use Prismic\Api;

class SearchFormTest extends \PHPUnit_Framework_TestCase
{

    private static $testRepository = 'http://frontwebconf.prismic.io/api';

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Forms require a ref to be set for all operations
     */
    public function testExceptionIsThrownWhenSubmittingAFormWithoutRef()
    {
        $api = Api::get(self::$testRepository);
        $searchForm = $api->forms()->everything;
        $searchForm->submit();
    }

}
