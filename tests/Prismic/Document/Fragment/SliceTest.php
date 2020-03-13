<?php
declare(strict_types=1);

namespace Prismic\Test\Document\Fragment;

use Prismic\Document\Fragment\FragmentCollection;
use Prismic\Document\Fragment\Group;
use Prismic\Document\Fragment\Slice;
use Prismic\Exception\InvalidArgumentException;
use Prismic\Test\FakeLinkResolver;
use Prismic\Test\TestCase;

class SliceTest extends TestCase
{

    /** @var FragmentCollection */
    private $collection;

    protected function setUp() : void
    {
        parent::setUp();
        $data = \json_decode($this->getJsonFixture('fragments/slices.json'));
        $this->collection = FragmentCollection::factory($data, new FakeLinkResolver());
    }

    public function testGetPrimaryIsFunctionalForAllSliceSpecs() : void
    {
        /** @var Slice $v2 */
        $v2 = $this->collection->get('slice-v2');
        $this->assertInstanceOf(Slice::class, $v2);
        $this->assertInstanceOf(FragmentCollection::class, $v2->getPrimary());
        $this->assertIsString($v2->asHtml());

        /** @var Slice $v1 */
        $v1 = $this->collection->get('composite-v1');
        $this->assertInstanceOf(Slice::class, $v1);
        $this->assertInstanceOf(FragmentCollection::class, $v1->getPrimary());
        $this->assertIsString($v1->asHtml());

        /** @var Group $sliceZone */
        $sliceZone = $this->collection->get('legacy-v1');
        $this->assertInstanceOf(Group::class, $sliceZone);
        $items = $sliceZone->getItems();
        /** @var Slice $slice */
        $slice = \current($items);
        $this->assertInstanceOf(Slice::class, $slice);
        $this->assertInstanceOf(FragmentCollection::class, $slice->getPrimary());
    }

    public function testGetItemsIsFunctionalForAllSliceSpecs() : void
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

    public function testMinimumSliceRequirementIsType() : void
    {
        /** @var Slice $slice */
        $slice = Slice::factory(\json_decode('{"slice_type":"some-type"}'), new FakeLinkResolver());
        $this->assertInstanceOf(Slice::class, $slice);
        $this->assertSame('some-type', $slice->getType());
        $this->assertNull($slice->getLabel());
        $this->assertInstanceOf(FragmentCollection::class, $slice->getPrimary());
        $this->assertInstanceOf(Group::class, $slice->getItems());
        $this->assertNull($slice->asText());
        $this->assertNull($slice->asHtml());
    }

    public function testExceptionThrownForMissingSliceType() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No Slice type could be determined from the payload');
        Slice::factory(\json_decode('{}'), new FakeLinkResolver());
    }

    public function testAsText() : void
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

    public function testAsHtml() : void
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
