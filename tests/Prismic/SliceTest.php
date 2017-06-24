<?php

namespace Prismic\Test;

use Prismic\Document;
use Prismic\Fragment\SliceZone;
use Prismic\Fragment\CompositeSlice;
use Prismic\Fragment\Group;
use Prismic\Fragment\GroupDoc;

class SliceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var stdClass
     */
    private $data;

    /**
     * @var Document
     */
    private $document;

    public function setUp()
    {
        $this->data = json_decode(file_get_contents(__DIR__ . '/../fixtures/repeatable-slices.json'));
        $this->document = Document::parse($this->data->results[0]);
    }

    public function testRepeatableSliceCanBeLocated()
    {
        $zone = $this->document->get('slice-repeat.my_zone');
        $this->assertInstanceOf(SliceZone::class, $zone);
        $slices = $zone->getSlices();
        $this->assertCount(2, $slices);
        $this->assertContainsOnlyInstancesOf(CompositeSlice::class, $slices);
    }

    /**
     * @depends testRepeatableSliceCanBeLocated
     */
    public function testCompositeSliceHasExpectedProperties()
    {
        $zone = $this->document->get('slice-repeat.my_zone');
        $slices = $zone->getSlices();
        $slice = current($slices);
        $this->assertSame('test_slice', $slice->getSliceType());
        $this->assertSame('Label Value', $slice->getLabel());
        $this->assertInstanceOf(GroupDoc::class, $slice->getPrimary());
        $this->assertInstanceOf(Group::class, $slice->getItems());
        $this->assertInternalType('string', $slice->asText());
        $this->assertInternalType('string', $slice->asHtml());
    }

    public function textContentValueProvider()
    {
        return [
            ['/RepeatValue1.1/'],
            ['/RepeatValue1.2/'],
            ['/RepeatValue2.1/'],
            ['/RepeatValue2.2/'],
            ['/NonRepeatValue1/'],
        ];
    }

    /**
     * @dataProvider textContentValueProvider
     */
    public function testCompositeSliceTextValuesAreRendered($match)
    {
        $zone = $this->document->get('slice-repeat.my_zone');
        $slices = $zone->getSlices();
        $slice  = current($slices);
        $this->assertContains($match, $slice->asText());
        $this->assertContains($match, $slice->asHtml());
    }

}
