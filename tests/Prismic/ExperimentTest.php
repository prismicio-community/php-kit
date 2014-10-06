<?php

namespace Prismic\Test;

use Prismic\Experiments;

class ExperimentTest extends \PHPUnit_Framework_TestCase
{
    private $experiments;

    protected function setUp()
    {
        $experimentsJson = json_decode(file_get_contents(__DIR__.'/../fixtures/experiments.json'));
        $this->experiments = Experiments::parse($experimentsJson);
    }

    public function testParsing()
    {
        $running = $this->experiments->getRunning();
        $exp1 = $running[0];
        $this->assertEquals("VDUBBawGAKoGelsX", $exp1->getId());
        $this->assertEquals("_UQtin7EQAOH5M34RQq6Dg", $exp1->getGoogleId());
        $this->assertEquals("Exp 1", $exp1->getName());

        $variations = $exp1->getVariations();
        $base = $variations[0];
        $this->assertEquals("VDUBBawGAKoGelsZ", $base->getId());
        $this->assertEquals("Base", $base->getLabel());
        $this->assertEquals("VDUBBawGALAGelsa", $base->getRef());
    }

    public function testCookieParsing()
    {
        $this->assertNull($this->experiments->refFromCookie(""), "Empty cookie");
        $this->assertNull($this->experiments->refFromCookie("Poneys are awesome"), "Invalid content");
        $this->assertEquals("VDUBBawGALAGelsa", $this->experiments->refFromCookie("_UQtin7EQAOH5M34RQq6Dg%200"), "Actual running variation index");
        $this->assertEquals("VDUUmHIKAZQKk9uq", $this->experiments->refFromCookie("_UQtin7EQAOH5M34RQq6Dg%201"), "Actual running variation index");
        $this->assertNull($this->experiments->refFromCookie("_UQtin7EQAOH5M34RQq6Dg%209"), "Index overflow");
        $this->assertNull($this->experiments->refFromCookie("_UQtin7EQAOH5M34RQq6Dg%20-1"), "Index overflow negative index");
        $this->assertNull($this->experiments->refFromCookie("NotAGoodLookingId%200"), "Unknown Google ID");
        $this->assertNull($this->experiments->refFromCookie("NotAGoodLookingId%201"), "Unknown Google ID");

    }

}
