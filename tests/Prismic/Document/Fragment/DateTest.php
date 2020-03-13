<?php
declare(strict_types=1);

namespace Prismic\Test\Document\Fragment;

use Prismic\Document\Fragment\Date;
use Prismic\Document\Fragment\FragmentCollection;
use Prismic\Test\FakeLinkResolver;
use Prismic\Test\TestCase;

class DateTest extends TestCase
{
    /** @var FragmentCollection */
    private $collection;

    protected function setUp() : void
    {
        parent::setUp();
        $this->collection = FragmentCollection::factory(
            \json_decode($this->getJsonFixture('fragments/date.json')),
            new FakeLinkResolver()
        );
    }

    public function testAllFixtureValuesAreDateInstances() : void
    {
        foreach ($this->collection->getFragments() as $fragment) {
            $this->assertInstanceOf(Date::class, $fragment);
        }
        $nonNull = ['date-v2', 'datetime-v2', 'date-v1', 'datetime-v1'];
        foreach ($nonNull as $key) {
            $fragment = $this->collection->get($key);
            /** @var Date $fragment */
            $this->assertInstanceOf(\DateTimeInterface::class, $fragment->asDateTime());
            $date = $fragment->asDateTime();
            $this->assertSame('2018-01-01', $date->format('Y-m-d'));
        }
    }

    public function testAsTextReturnsStringOrNull() : void
    {
        $date = $this->collection->get('date-v2');
        $this->assertSame('2018-01-01', $date->asText());

        $date = $this->collection->get('datetime-v2');
        $this->assertSame('2018-01-01T12:00:00+0000', $date->asText());

        $date = $this->collection->get('null-date-v1');
        $this->assertNull($date->asText());
    }

    public function testAsHtmlReturnsExpectedValue() : void
    {
        $date = $this->collection->get('date-v2');
        $expect = '<time datetime="2018-01-01">2018-01-01</time>';
        $this->assertSame($expect, $date->asHtml());

        $date = $this->collection->get('null-date-v1');
        $this->assertNull($date->asHtml());
    }
}
