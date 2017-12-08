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

    /**
     * @depends testCorrectRefIsReturned
     */
    public function testInPreviewIsTrueWhenPreviewCookieIsSet()
    {
        $cookieValue = 'Preview Ref Cookie Value';
        $_COOKIE = [
            'io.prismic.preview' => $cookieValue,
        ];
        $this->assertTrue($this->api->inPreview());
    }

    /**
     * @depends testCorrectRefIsReturned
     */
    public function testInExperimentIsTrueWhenExperimentCookieIsSet()
    {
        $cookieValue = 'Experiment Cookie Value';
        $this->experiments->method('refFromCookie')->willReturn('Experiment Ref');
        $_COOKIE = [
            'io.prismic.experiment' => $cookieValue,
        ];
        $this->assertTrue($this->api->inExperiment());
    }

    /**
     * @depends testInExperimentIsTrueWhenExperimentCookieIsSet
     */
    public function testPreviewRefTrumpsExperimentRefWhenSet()
    {
        $this->experiments->method('refFromCookie')->willReturn('Experiment Ref');
        $_COOKIE = [
            'io.prismic.experiment' => 'Experiment Cookie Value',
            'io.prismic.preview'    => 'Preview Ref Cookie Value',
        ];
        $this->assertTrue($this->api->inPreview());
        $this->assertFalse($this->api->inExperiment());
    }

    public function testInPreviewAndInExperimentIsFalseWhenNoCookiesAreSet()
    {
        $_COOKIE = [];
        $this->assertFalse($this->api->inPreview());
        $this->assertFalse($this->api->inExperiment());
    }


}
