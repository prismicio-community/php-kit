<?php

namespace Prismic\Test;

use Prismic;

class ApiTest extends \PHPUnit_Framework_TestCase
{

    private $api;

    private $experiments;

    public function setUp()
    {
        $this->api = $this->getMockBuilder(Prismic\Api::class)
            ->disableOriginalConstructor()
            ->setMethods(['master', 'getExperiments'])
            ->getMock();

        $master = new Prismic\Ref('Master', 'Master-Ref-String', 'Master', true, null);

        $this->api
             ->method('master')
             ->willReturn($master);

        $this->experiments = $this->getMockBuilder(Prismic\Experiments::class)
             ->disableOriginalConstructor()
             ->setMethods(['refFromCookie'])
             ->getMock();

        $this->api
             ->method('getExperiments')
             ->willReturn($this->experiments);
    }

    public function testRef()
    {
        $this->assertSame('Master-Ref-String', $this->api->ref());
    }

    public function getCookieData()
    {
        return [
            [
                [
                    'io.prismic.preview' => 'preview',
                    'other' => 'other',
                ],
                'preview'
            ],
            [
                [
                    'io.prismic.preview' => 'preview',
                    'io.prismic.experiment' => 'experiment',
                ],
                'preview'
            ],
            [
                [
                    'io.prismic.experiment' => 'experiment',
                    'other' => 'other',
                ],
                'experiment'
            ],
            [
                [
                    'foo' => 'foo',
                    'other' => 'other',
                ],
                'Master-Ref-String'
            ],
        ];
    }

    /**
     * @dataProvider getCookieData
     */
    public function testCorrectRefIsReturned($cookie, $expect)
    {
        // Make sure that Prismic\Experiments::refFromCookie returns a 'valid' ref
        $this->experiments->method('refFromCookie')->willReturn('experiment');
        $_COOKIE = $cookie;
        $this->assertSame($expect, $this->api->ref());
    }

    public function testRefDoesNotReturnStaleExperimentRef()
    {
        // Make sure that Prismic\Experiments::refFromCookie returns null, i.e. no experiment running
        $this->experiments->method('refFromCookie')->willReturn(null);
        $_COOKIE = [
            'io.prismic.experiment' => 'Stale Experiment Cookie Value',
        ];
        $this->assertSame('Master-Ref-String', $this->api->ref());
    }

    private function getCacheMock()
    {
        return $this->getMockBuilder(Prismic\Cache\ApcCache::class)
                    ->setMethods(['get'])
                    ->getMock();
    }

    public function testCachedApiDataIsConditionallyUnserialized()
    {
        $data  = new Prismic\ApiData(
            [], [], [], [], [], new Prismic\Experiments([], []), '', ''
        );
        $cache = $this->getCacheMock();
        $cache->method('get')->willReturn($data);
        $api = Prismic\Api::get('foo', 'foo', null, $cache);
        $this->assertInstanceOf(Prismic\Api::class, $api);
        $this->assertSame($data, $api->getData());

        $serialized = serialize($data);
        $cache = $this->getCacheMock();
        $cache->method('get')->willReturn($serialized);

        $api = Prismic\Api::get('foo', 'foo', null, $cache);
        $this->assertInstanceOf(Prismic\Api::class, $api);
        $this->assertInstanceOf(Prismic\ApiData::class, $api->getData());
    }

}
