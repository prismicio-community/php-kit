<?php

namespace Prismic\Test;

class CacheTest extends \PHPUnit_Framework_TestCase
{
    private $cache;

    protected function setUp()
    {
        $this->cache = new \Prismic\Cache\DefaultCache();
    }

    public function testSetGetValue()
    {
        $this->cache->set('key', 'value');
        $this->assertEquals($this->cache->get('key'), 'value');
    }

    public function testSetDeleteValue()
    {
        $this->cache->set('key', 'value');
        $this->assertEquals($this->cache->get('key'), 'value');
        $this->cache->delete('key');
        $this->assertFalse($this->cache->get('key'));
    }

    public function testSetValueClear()
    {
        $this->cache->set('key', 'value');
        $this->cache->set('key1', 'value1');
        $this->cache->set('key2', 'value2');
        $this->assertEquals($this->cache->get('key'), 'value');
        $this->assertEquals($this->cache->get('key1'), 'value1');
        $this->assertEquals($this->cache->get('key2'), 'value2');
        $this->cache->clear();
        $this->assertFalse($this->cache->get('key'));
        $this->assertFalse($this->cache->get('key1'));
        $this->assertFalse($this->cache->get('key2'));
    }
}
