<?php

namespace Prismic\Test;

use Prismic\Api;
use Prismic\Response;

class ApiTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \RuntimeException
     */
    public function testUnableToDecode()
    {
        $response = $this->getMockBuilder('Guzzle\Http\Message\Response')
            ->disableOriginalConstructor()
            ->getMock();
        $response->expects($this->once())->method('getBody')->will($this->returnValue("not a json"));

        $request = $this->getMock('Guzzle\Http\Message\RequestInterface');
        $request->expects($this->once())->method('send')->will($this->returnValue($response));

        $client = $this->getMock('Guzzle\Http\Client');
        $client->expects($this->once())->method('get')->will($this->returnValue($request));

        Api::get('don\'t care about this value', null, $client);
    }

    public function testValidApiCall()
    {
        $response = $this->getMockBuilder('Guzzle\Http\Message\Response')
            ->disableOriginalConstructor()
            ->getMock();
        $response->expects($this->once())->method('getBody')->will($this->returnValue(file_get_contents(__DIR__.'/../fixtures/data.json')));

        $request = $this->getMock('Guzzle\Http\Message\RequestInterface');
        $request->expects($this->once())->method('send')->will($this->returnValue($response));

        $client = $this->getMock('Guzzle\Http\Client');
        $client->expects($this->once())->method('get')->will($this->returnValue($request));

        $api = Api::get('don\'t care about this value', null, $client);

        $this->assertInstanceOf('Prismic\Api', $api);

        return $api;
    }

    /**
     * @param Api $response
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
