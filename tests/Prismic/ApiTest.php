<?php

namespace Prismic\Test;

use Prismic\Api;
use Prismic\Cache\ApcCache;

class ApiTest extends \PHPUnit_Framework_TestCase
{

    protected function setUp()
    {
        $cache = new ApcCache();
        $cache->clear();
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testUnableToDecode()
    {
        $response = $this->getMockBuilder('Ivory\HttpAdapter\Message\Response')
            ->disableOriginalConstructor()
            ->getMock();
        $response->expects($this->once())->method('getBody')->will($this->returnValue("not a json"));

        $httpAdapter = $this->getMock('Ivory\HttpAdapter\HttpAdapterInterface');
        $httpAdapter->expects($this->once())->method('get')->will($this->returnValue($response));

        Api::get('don\'t care about this value', null, $httpAdapter);
    }

    public function testValidApiCall()
    {
        $response = $this->getMockBuilder('Ivory\HttpAdapter\Message\Response')
            ->disableOriginalConstructor()
            ->getMock();
        $response->expects($this->once())->method('getBody')->will($this->returnValue(file_get_contents(__DIR__.'/../fixtures/data.json')));

        $httpAdapter = $this->getMock('Ivory\HttpAdapter\HttpAdapterInterface');
        $httpAdapter->expects($this->once())->method('get')->will($this->returnValue($response));

        $api = Api::get('don\'t care about this value', null, $httpAdapter);

        $this->assertInstanceOf('Prismic\Api', $api);
        $this->assertEquals($httpAdapter, $api->getHttpAdapter());

        return $api;
    }

    /**
     * @param Api $api
     *
     * @depends testValidApiCall
     */
    public function testForm(Api $api)
    {
        $forms = $api->forms();

        $this->assertObjectHasAttribute('everything', $forms);

//        $forms->everything->submit();
    }
}
