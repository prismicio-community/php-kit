<?php
declare(strict_types=1);

namespace Prismic\Test;

use DateTimeImmutable;
use Prismic\Exception\ExceptionInterface;
use Prismic\Ref;
use stdClass;

class RefTest extends TestCase
{

    private $refs;

    public function getRefs() : iterable
    {
        if (! $this->refs) {
            $this->refs = \json_decode($this->getJsonFixture('refs.json'));
        }
        $out = [];
        foreach ($this->refs->refs as $ref) {
            $out[] = [$ref];
        }
        return $out;
    }

    /**
     * @dataProvider getRefs
     */
    public function testParseRefs($json) : void
    {
        $ref = Ref::parse($json);
        $this->assertIsString($ref->getId());
        $this->assertStringMatchesFormat('%s', $ref->getId());
        $this->assertIsString($ref->getRef());
        $this->assertStringMatchesFormat('%s', $ref->getRef());
        $this->assertIsString($ref->getLabel());
        $this->assertStringMatchesFormat('%s', $ref->getLabel());
        $this->assertIsBool($ref->isMasterRef());
        if ($ref->getScheduledAt() !== null) {
            $this->assertIsInt($ref->getScheduledAt());
            $this->assertEquals(13, strlen((string)$ref->getScheduledAt()), 'Expected a 13 digit number');
        }
    }

    /**
     * @dataProvider getRefs
     */
    public function testGetScheduledAtTimestamp($json) : void
    {
        $ref = Ref::parse($json);

        if ($ref->getScheduledAtTimestamp() !== null) {
            $this->assertIsInt($ref->getScheduledAtTimestamp());
            $this->assertEquals(10, strlen((string)$ref->getScheduledAtTimestamp()), 'Expected a 10 digit number');
        } else {
            // Squash No assertions warning in PHP Unit
            $this->assertNull($ref->getScheduledAtTimestamp());
        }
    }

    /**
     * @dataProvider getRefs
     */
    public function testToStringSerialisesToRef($json) : void
    {
        $ref = Ref::parse($json);
        $this->assertSame($ref->getRef(), (string) $ref);
    }

    /**
     * @dataProvider getRefs
     */
    public function testGetScheduledDate($json) : void
    {
        $ref = Ref::parse($json);
        if ($ref->getScheduledAtTimestamp() !== null) {
            $date = $ref->getScheduledDate();
            $this->assertInstanceOf(DateTimeImmutable::class, $date);
            $this->assertSame($ref->getScheduledAtTimestamp(), $date->getTimestamp());
            $this->assertNotSame($date, $ref->getScheduledDate(), 'Returned date should be a new instance every time');
        } else {
            $this->assertNull($ref->getScheduledDate());
        }
    }

    public function testExceptionThrownForInvalidJsonObject()
    {
        $this->expectException(ExceptionInterface::class);
        Ref::parse(new stdClass);
    }
}
