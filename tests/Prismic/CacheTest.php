<?php

namespace Prismic\Test;

use Prismic\LruCacheAdapter;

class CacheTest extends \PHPUnit_Framework_TestCase
{
    protected $cache;

    protected function setUp()
    {
        $this->cache = new LruCacheAdapter(5);
    }

    public function testCheckInitializationIsRight()
    {
        $this->assertEquals(0, $this->cache->size());
    }

    public function testCheckAddingCacheEntrie()
    {
        $this->cache->save('key1', 'value1');
        $this->cache->save('key2', 'value2');
        $this->cache->save('key3', 'value3');
        $this->cache->save('key4', 'value4');
        $this->cache->save('key5', 'value5');
        $this->cache->save('key6', 'value6');
        $this->assertEquals(5, $this->cache->size());

        // checking that key1 is the one that's gone
        $this->assertTrue($this->cache->contains('key2') && $this->cache->contains('key3') && $this->cache->contains('key4') && $this->cache->contains('key5') && $this->cache->contains('key6') && !$this->cache->contains('key1'));
    }

    public function testFetchingAndReordering()
    {
        $this->cache->save('key1', 'value1');
        $this->cache->save('key2', 'value2');
        $this->cache->save('key3', 'value3');
        $this->cache->save('key4', 'value4');
        $this->assertEquals('value1', $this->cache->fetch('key1')); // fetching key1 now puts it back at the end of the array, so it doesn't get deleted too soon
        $this->cache->save('key5', 'value5');
        $this->cache->save('key6', 'value6');
        $this->assertEquals(5, $this->cache->size());

        // checking that key2 is the one that's gone
        $this->assertTrue($this->cache->contains('key1') && $this->cache->contains('key3') && $this->cache->contains('key4') && $this->cache->contains('key5') && $this->cache->contains('key6') && !$this->cache->contains('key2'));
    }

}
