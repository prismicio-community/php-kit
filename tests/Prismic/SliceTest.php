<?php

namespace Prismic\Test;

use Prismic\Document;
use Prismic\Fragment\SliceZone;

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
    }

}
