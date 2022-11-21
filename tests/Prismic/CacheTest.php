<?php
declare(strict_types=1);

namespace Prismic\Test;

use Prismic\Cache\ApcCache;

class CacheTest extends TestCase
{
    private $cache;

    protected function setUp(): void
    {
        $this->cache = new ApcCache();
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
        $this->assertNull($this->cache->get('key'));
    }

    public function testSetValueClearHas()
    {
        $this->cache->set('key', 'value');
        $this->assertTrue($this->cache->has('key'));
        $this->assertFalse($this->cache->has('key1'));
        $this->assertFalse($this->cache->has('key2'));
        $this->cache->set('key1', 'value1');
        $this->cache->set('key2', 'value2');
        $this->assertTrue($this->cache->has('key'));
        $this->assertTrue($this->cache->has('key1'));
        $this->assertTrue($this->cache->has('key2'));
        $this->assertEquals($this->cache->get('key'), 'value');
        $this->assertEquals($this->cache->get('key1'), 'value1');
        $this->assertEquals($this->cache->get('key2'), 'value2');
        $this->cache->clear();
        $this->assertFalse($this->cache->has('key'));
        $this->assertFalse($this->cache->has('key1'));
        $this->assertFalse($this->cache->has('key2'));
        $this->assertNull($this->cache->get('key'));
        $this->assertNull($this->cache->get('key1'));
        $this->assertNull($this->cache->get('key2'));
    }

    public function testSetGetReturnsExpectedValue()
    {
        $data = \json_decode($this->getJsonFixture('data.json'));
        $this->cache->set('key', $data);
        $result = $this->cache->get('key');
        $this->assertEquals($data, $result);
    }

    public function testLongUrlBasedCacheKeysArePersistedCorrectly()
    {
        $data = \json_decode($this->getJsonFixture('data.json'));
        $url = $data->forms->everything->action;
        $url .= '?access_token=AVeryLongAccessTokenForPermanentAccessToTheRepository&q=SomeQueryString';
        $this->cache->set($url, $data);
        $result = $this->cache->get($url);
        $this->assertEquals($data, $result);
    }
}
