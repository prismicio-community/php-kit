<?php
declare(strict_types=1);

namespace Prismic\Test\Dom;

use Prismic\Test\TestCase;
use Prismic\Dom\Date;
use \DateTime;
use \DateTimeZone;

class DateTest extends TestCase
{
    public function testDateAsDate()
    {
        $date = '2017-02-17';
        $expected = new DateTime($date, new DateTimeZone('UTC'));
        $actual = Date::asDate($date);
        $this->assertEquals($expected, $actual);
    }

    public function testTimestampAsDate()
    {
        $timestamp = '2017-02-17T12:30:00+0000';
        $expected = new DateTime($timestamp, new DateTimeZone('UTC'));
        $actual = Date::asDate($timestamp);
        $this->assertEquals($expected, $actual);
    }
}
