<?php
declare(strict_types=1);

namespace Prismic\Test\Document\Fragment;

use Prismic\Document\Fragment\FragmentCollection;
use Prismic\Document\Fragment\GeoPoint;
use Prismic\Exception\InvalidArgumentException;
use Prismic\Test\FakeLinkResolver;
use Prismic\Test\TestCase;

class GeoPointTest extends TestCase
{
    public function testFactoryThrowsExceptionForInvalidObject() : void
    {
        $this->expectException(InvalidArgumentException::class);
        GeoPoint::factory('foo', new FakeLinkResolver());
    }

    public function testValidGeoPointSpecs() : void
    {
        $collection = FragmentCollection::factory(
            \json_decode($this->getJsonFixture('fragments/geopoint.json')),
            new FakeLinkResolver()
        );
        /** @var FragmentCollection $collection */
        foreach ($collection->getFragments() as $point) {
            /** @var GeoPoint $point */
            $this->assertInstanceOf(GeoPoint::class, $point);
            $this->assertSame(1.1, $point->getLatitude());
            $this->assertSame(2.2, $point->getLongitude());

            $expect = '<span class="geopoint" data-latitude="1.1" data-longitude="2.2">1.1, 2.2</span>';
            $this->assertSame($expect, $point->asHtml());
            $this->assertStringMatchesFormat('%f, %f', $point->asText());

            $object = \json_decode(\json_encode($point));
            $this->assertSame(1.1, $object->latitude);
            $this->assertSame(2.2, $object->longitude);
        }
    }
}
