<?php
declare(strict_types=1);

namespace Prismic\Test\Document\Fragment;

use Prismic\Document\Fragment\Color;
use Prismic\Document\Fragment\FragmentCollection;
use Prismic\Document\Fragment\Group;
use Prismic\Exception\InvalidArgumentException;
use Prismic\Test\FakeLinkResolver;
use Prismic\Test\TestCase;

class GroupTest extends TestCase
{
    public function testNonArrayCausesExceptionInFactory() : void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected an indexed array for group construction');
        Group::factory('foo', new FakeLinkResolver());
    }

    public function testEmptyGroup() : void
    {
        /** @var Group $group */
        $group = Group::factory([], new FakeLinkResolver());
        $this->assertCount(0, $group->getItems());
        $this->assertNull($group->asHtml());
        $this->assertNull($group->asText());
    }

    public function testBasicGroup() : void
    {
        $data = \json_decode($this->getJsonFixture('fragments/group.json'));
        /** @var Group $group */
        $group = Group::factory($data, new FakeLinkResolver());
        $items = $group->getItems();
        $this->assertCount(2, $items);
        $this->assertContainsOnlyInstancesOf(FragmentCollection::class, $items);
        /** @var FragmentCollection $collection */
        foreach ($items as $collection) {
            $this->assertInstanceOf(Color::class, $collection->get('color'));
            $this->assertSame("#000000\nText", $collection->asText());
            $this->assertSame("#000000\nText", $collection->asHtml());
        }
        $this->assertSame("#000000\nText\n#000000\nText", $group->asText());
        $this->assertSame("#000000\nText\n#000000\nText", $group->asHtml());
    }
}
