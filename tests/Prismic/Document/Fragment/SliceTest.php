<?php
declare(strict_types=1);

namespace Prismic\Test\Document\Fragment;

use Prismic\Document\Fragment\FragmentCollection;
use Prismic\Document\Fragment\Group;
use Prismic\Document\Fragment\Slice;
use Prismic\Test\FakeLinkResolver;
use Prismic\Test\TestCase;

class SliceTest extends TestCase
{

    /** @var FragmentCollection */
    private $collection;

    public function setUp()
    {
        parent::setUp();
        $data = \json_decode($this->getJsonFixture('fragments/slices.json'));
        $this->collection = FragmentCollection::factory($data, new FakeLinkResolver());
    }

    public function testGetPrimaryIsFunctionalForAllSliceSpecs()
    {
        /** @var Slice $v2 */
        $v2 = $this->collection->get('slice-v2');
        $this->assertInstanceOf(Slice::class, $v2);
        $this->assertInstanceOf(FragmentCollection::class, $v2->getPrimary());
        $this->assertInternalType('string', $v2->asHtml());

        /** @var Slice $v1 */
        $v1 = $this->collection->get('composite-v1');
        $this->assertInstanceOf(Slice::class, $v1);
        $this->assertInstanceOf(FragmentCollection::class, $v1->getPrimary());
        $this->assertInternalType('string', $v1->asHtml());

        /** @var Group $sliceZone */
        $sliceZone = $this->collection->get('legacy-v1');
        $this->assertInstanceOf(Group::class, $sliceZone);
        $items = $sliceZone->getItems();
        /** @var Slice $slice */
        $slice = \current($items);
        $this->assertInstanceOf(Slice::class, $slice);
        $this->assertNull($slice->getPrimary());
    }

    public function testGetItemsIsFunctionalForAllSliceSpecs()
    {
        /** @var Slice $v2 */
        $v2 = $this->collection->get('slice-v2');
        $this->assertInstanceOf(Group::class, $v2->getItems());

        /** @var Slice $v1 */
        $v1 = $this->collection->get('composite-v1');
        $this->assertInstanceOf(Group::class, $v1->getItems());

        /** @var Group $sliceZone */
        $sliceZone = $this->collection->get('legacy-v1');
        $items = $sliceZone->getItems();
        /** @var Slice $slice */
        $slice = \current($items);
        $this->assertInstanceOf(Group::class, $slice->getItems());
    }

    public function testMinimumSliceRequirementIsType()
    {
        /** @var Slice $slice */
        $slice = Slice::factory(\json_decode('{"slice_type":"some-type"}'), new FakeLinkResolver());
        $this->assertInstanceOf(Slice::class, $slice);
        $this->assertSame('some-type', $slice->getType());
        $this->assertNull($slice->getLabel());
        $this->assertNull($slice->getPrimary());
        $this->assertNull($slice->getItems());
        $this->assertNull($slice->asText());
        $this->assertNull($slice->asHtml());
    }

    /**
     * @expectedException \Prismic\Exception\InvalidArgumentException
     * @expectedExceptionMessage No Slice type could be determined from the payload
     */
    public function testExceptionThrownForMissingSliceType()
    {
        Slice::factory(\json_decode('{}'), new FakeLinkResolver());
    }

    public function testAsText()
    {
        /** @var Slice $v2 */
        $v2 = $this->collection->get('slice-v2');
        $expect = \implode(\PHP_EOL, [
            '2018-01-01',
            'Text',
            '1.100000, 2.200000',
            'Text 1',
            'Text 2',
            'Text 1',
            'Text 2',
        ]);
        $this->assertSame($expect, $v2->asText());
    }

    public function testAsHtml()
    {
        $sliceZone = $this->collection->get('legacy-v1');
        $expect = \implode(\PHP_EOL, [
            '<div data-slice-type="legacy-v1" class="label-value">',
            'Text 1',
            'Text 2',
            'Text 1',
            'Text 2',
            '</div>',
        ]);
        $this->assertSame($expect, $sliceZone->asHtml());
    }
}
