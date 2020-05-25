<?php
declare(strict_types=1);

namespace Prismic\Test\Document\Fragment;

use Prismic\Document\Fragment\FragmentCollection;
use Prismic\Document\Fragment\Group;
use Prismic\Document\Fragment\Slice;
use Prismic\Exception\InvalidArgumentException;
use Prismic\Json;
use Prismic\Test\FakeLinkResolver;
use Prismic\Test\TestCase;
use function assert;
use function current;
use function implode;
use function json_decode;
use const PHP_EOL;

class SliceTest extends TestCase
{
    /** @var FragmentCollection */
    private $collection;

    protected function setUp() : void
    {
        parent::setUp();
        $data = Json::decodeObject($this->getJsonFixture('fragments/slices.json'));
        $this->collection = FragmentCollection::factory($data, new FakeLinkResolver());
    }

    public function testGetPrimaryIsFunctionalForAllSliceSpecs() : void
    {
        $v2 = $this->collection->get('slice-v2');
        assert($v2 instanceof Slice);
        $this->assertInstanceOf(Slice::class, $v2);
        $this->assertInstanceOf(FragmentCollection::class, $v2->getPrimary());
        $this->assertIsString($v2->asHtml());

        $v1 = $this->collection->get('composite-v1');
        assert($v1 instanceof Slice);
        $this->assertInstanceOf(Slice::class, $v1);
        $this->assertInstanceOf(FragmentCollection::class, $v1->getPrimary());
        $this->assertIsString($v1->asHtml());

        $sliceZone = $this->collection->get('legacy-v1');
        assert($sliceZone instanceof Group);
        $this->assertInstanceOf(Group::class, $sliceZone);
        $items = $sliceZone->getItems();
        $slice = current($items);
        assert($slice instanceof Slice);
        $this->assertInstanceOf(Slice::class, $slice);
        $this->assertInstanceOf(FragmentCollection::class, $slice->getPrimary());
    }

    public function testGetItemsIsFunctionalForAllSliceSpecs() : void
    {
        $v2 = $this->collection->get('slice-v2');
        assert($v2 instanceof Slice);
        $this->assertInstanceOf(Group::class, $v2->getItems());

        $v1 = $this->collection->get('composite-v1');
        assert($v1 instanceof Slice);
        $this->assertInstanceOf(Group::class, $v1->getItems());

        $sliceZone = $this->collection->get('legacy-v1');
        assert($sliceZone instanceof Group);
        $items = $sliceZone->getItems();
        $slice = current($items);
        assert($slice instanceof Slice);
        $this->assertInstanceOf(Group::class, $slice->getItems());
    }

    public function testMinimumSliceRequirementIsType() : void
    {
        $slice = Slice::factory(json_decode('{"slice_type":"some-type"}'), new FakeLinkResolver());
        assert($slice instanceof Slice);
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
        Slice::factory(Json::decodeObject('{}'), new FakeLinkResolver());
    }

    public function testAsText() : void
    {
        $v2 = $this->collection->get('slice-v2');
        assert($v2 instanceof Slice);
        $expect = implode(PHP_EOL, [
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
        $expect = implode(PHP_EOL, [
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
