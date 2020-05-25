<?php
declare(strict_types=1);

namespace Prismic\Test\Cache;

use Prismic\ApiData;
use Prismic\Cache\DefaultCache;
use Prismic\Form;
use Prismic\Test\TestCase;
use Psr\Cache\CacheItemPoolInterface;
use function assert;

class CacheRoundTripTest extends TestCase
{
    /** @var ApiData */
    private $apiData;

    /** @var CacheItemPoolInterface */
    private $cache;

    protected function setUp() : void
    {
        parent::setUp();
        $this->apiData = ApiData::withJsonString($this->getJsonFixture('data.json'));
        $this->cache = DefaultCache::factory();
        $this->cache->clear();
    }

    public function testApiDataCanBeReHydratedFromCacheSuccessfully() : void
    {
        $item = $this->cache->getItem('ApiDataTest');
        $this->assertFalse($item->isHit());
        $item->set($this->apiData);
        $this->cache->save($item);

        $item = $this->cache->getItem('ApiDataTest');
        $this->assertTrue($item->isHit());
        $data = $item->get();
        $this->assertInstanceOf(ApiData::class, $data);
        assert($data instanceof ApiData);
        $this->assertNotSame($this->apiData, $data);

        $this->assertInstanceOf(Form::class, $this->apiData->getForms()['blogs']);
        $this->assertInstanceOf(Form::class, $data->getForms()['blogs']);
        $this->assertNotSame(
            $this->apiData->getForms()['blogs'],
            $data->getForms()['blogs']
        );
    }
}
