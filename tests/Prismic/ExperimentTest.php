<?php
declare(strict_types=1);

namespace Prismic\Test;

use Prismic\Experiment;
use Prismic\Experiments;

class ExperimentTest extends TestCase
{
    /**
     * @var Experiments
     */
    private $experiments;

    protected function setUp() : void
    {
        $experimentsJson = \json_decode($this->getJsonFixture('experiments.json'));
        $this->experiments = Experiments::parse($experimentsJson);
    }

    public function testParsing() : void
    {
        $running = $this->experiments->getRunning();
        $this->assertContainsOnlyInstancesOf(Experiment::class, $running);
        $this->assertContainsOnlyInstancesOf(Experiment::class, $this->experiments->getDraft());

        $exp1 = $running[0];
        $this->assertSame($exp1, $this->experiments->getCurrent());

        $this->assertEquals("VDUBBawGAKoGelsX", $exp1->getId());
        $this->assertEquals("_UQtin7EQAOH5M34RQq6Dg", $exp1->getGoogleId());
        $this->assertEquals("Exp 1", $exp1->getName());

        $variations = $exp1->getVariations();
        $base = $variations[0];
        $this->assertEquals("VDUBBawGAKoGelsZ", $base->getId());
        $this->assertEquals("Base", $base->getLabel());
        $this->assertEquals("VDUBBawGALAGelsa", $base->getRef());
    }

    public function testCookieParsing() : void
    {
        $this->assertNull($this->experiments->refFromCookie(""), "Empty cookie");
        $this->assertNull($this->experiments->refFromCookie("Poneys are awesome"), "Invalid content");
        $this->assertEquals("VDUBBawGALAGelsa", $this->experiments->refFromCookie("_UQtin7EQAOH5M34RQq6Dg 0"), "Actual running variation index");
        $this->assertEquals("VDUUmHIKAZQKk9uq", $this->experiments->refFromCookie("_UQtin7EQAOH5M34RQq6Dg 1"), "Actual running variation index");
        $this->assertNull($this->experiments->refFromCookie("_UQtin7EQAOH5M34RQq6Dg 9"), "Index overflow");
        $this->assertNull($this->experiments->refFromCookie("_UQtin7EQAOH5M34RQq6Dg -1"), "Index overflow negative index");
        $this->assertNull($this->experiments->refFromCookie("NotAGoodLookingId 0"), "Unknown Google ID");
        $this->assertNull($this->experiments->refFromCookie("NotAGoodLookingId 1"), "Unknown Google ID");
    }

    public function testEmptyExperimentData() : void
    {
        $data = '{"draft":[],"running":[]}';
        $experiments = Experiments::parse(\json_decode($data));

        $this->assertNull($experiments->getCurrent());
    }
}
