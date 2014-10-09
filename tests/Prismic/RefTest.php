<?php

namespace Prismic\Test;

use Prismic\Ref;

class RefTest extends \PHPUnit_Framework_TestCase
{

    private $refs;

    public function getRefs()
    {
        if(!$this->refs) {
            $this->refs = json_decode(file_get_contents(__DIR__.'/../fixtures/refs.json'));
        }
        $out = array();
        foreach($this->refs->refs as $ref) {
            $out[] = array($ref);
        }
        return $out;
    }

    /**
     * @dataProvider getRefs
     */
    public function testParseRefs($json)
    {
        $ref = Ref::parse($json);
        $this->assertInternalType('string', $ref->getId());
        $this->assertStringMatchesFormat('%s', $ref->getId());
        $this->assertInternalType('string', $ref->getRef());
        $this->assertStringMatchesFormat('%s', $ref->getRef());
        $this->assertInternalType('string', $ref->getLabel());
        $this->assertStringMatchesFormat('%s', $ref->getLabel());
        $this->assertInternalType('boolean', $ref->isMasterRef());
        if(!is_null($ref->getScheduledAt())) {
            $this->assertInternalType('int', $ref->getScheduledAt());
            $this->assertEquals(13, strlen($ref->getScheduledAt()), 'Expected a 13 digit number');
        }
    }

    /**
     * @dataProvider getRefs
     */
    public function testGetScheduledAtTimestamp($json)
    {
        $ref = Ref::parse($json);
        if(!is_null($ref->getScheduledAtTimestamp())) {
            $this->assertInternalType('int', $ref->getScheduledAtTimestamp());
            $this->assertEquals(10, strlen($ref->getScheduledAtTimestamp()), 'Expected a 10 digit number');
        }
    }

    /**
     * @dataProvider getRefs
     */
    public function testToStringSerialisesToRef($json)
    {
        $ref = Ref::parse($json);
        $this->assertSame($ref->getRef(), (string) $ref);
    }

    /**
     * @dataProvider getRefs
     */
    public function testGetScheduledDate($json)
    {
        $ref = Ref::parse($json);
        if(!is_null($ref->getScheduledAtTimestamp())) {
            $date = $ref->getScheduledDate();
            $this->assertInstanceOf('DateTime', $date);
            $this->assertSame($ref->getScheduledAtTimestamp(), $date->getTimestamp());
            $this->assertNotSame($date, $ref->getScheduledDate(), 'Returned date should be a new instance every time');
        } else {
            $this->assertNull($ref->getScheduledDate());
        }
    }

}
