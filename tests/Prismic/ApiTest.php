<?php

namespace Prismic\Test;

use Prismic\Api;
use Prismic\Response;

class ApiTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Guzzle\Http\Exception\CurlException
     * @expectedMessage [curl] 6: Couldn't resolve host 'goog' [url] http://goog
     */
    public function testExceptionUnableToSolveHost()
    {
        $api = new Api('sd');
        $api->get('http://goog');
    }

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

        $api = new Api('sd', $client);
        $api->get('don\'t care about this value');
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

        $api = new Api('sd', $client);

        $response = $api->get('don\'t care about this value');

        $this->assertInstanceOf('Prismic\Response', $response);

        return $response;
    }

    /**
     * @param Response $response
     * @depends testValidApiCall
     */
    public function testForm(Response $response)
    {
        $forms = $response->forms();

        $this->assertObjectHasAttribute('everything', $forms);

//        $forms->everything->submit();
    }
}
