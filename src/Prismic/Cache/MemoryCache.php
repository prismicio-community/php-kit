<?php

namespace Prismic\Cache;

class MemoryCache implements CacheInterface
{
    private array $cache;

    public function has($key)
    {
        return isset($this->cache[$key]);
    }

    public function get($key)
    {
        return $this->cache[$key] ?? null;
    }

    public function set($key, $value, $ttl = 0)
    {
        $this->cache[$key] = $value;
    }

    public function delete($key)
    {
        unset($this->cache[$key]);
    }

    public function clear()
    {
        $this->cache = [];
    }
}
