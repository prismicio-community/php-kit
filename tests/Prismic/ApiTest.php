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

        $this->experiments
             ->method('refFromCookie')
             ->willReturn('experiment');

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
        $_COOKIE = $cookie;
        $this->assertSame($expect, $this->api->ref());
    }

}
