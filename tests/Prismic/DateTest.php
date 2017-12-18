<?php

namespace Prismic\Test;

use Prismic\Dom\Date;

class DateTest extends \PHPUnit_Framework_TestCase
{
    public function testAsDate()
    {
        $this->assertNull(Date::asDate());

        $date = '2017-02-17';
        $expected = new \DateTime($date);
        $actual = Date::asDate($date);
        $this->assertEquals($expected, $actual);

        $timestamp = '2017-02-17T12:30:00+0000';
        $expected = new \DateTime($timestamp);
        $actual = Date::asDate($timestamp);
        $this->assertEquals($expected, $actual);
    }
}
