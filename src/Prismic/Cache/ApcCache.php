<?php

namespace Prismic\Cache;

/**
 * The default implementation that is passed in the Api object when created:
 * it is based on APCU, and therefore requires APCU to be installed on the server.
 */
class ApcCache implements CacheInterface
{
    /**
     * Tests whether the cache has a value for a particular key
     *
     * @param string $key the key of the cache entry
     * @return boolean true if the cache has a value for this key, otherwise false
     */
    public function has($key)
    {
        return \apcu_exists($key);
    }

    /**
     * Returns the value of a cache entry from its key
     *
     * @param  string    $key the key of the cache entry
     * @return mixed the value of the entry, as it was passed to CacheInterface::set, null if not present in cache
     */
    public function get($key)
    {
        $value = \apcu_fetch($key, $success);
        if (! $success) {
            return null;
        }
        return $value;
    }

    /**
     * Stores a new cache entry
     *
     * @param string    $key   the key of the cache entry
     * @param mixed $value the value of the entry
     * @param int       $ttl   the time until this cache entry expires
     * @return void
     */
    public function set($key, $value, $ttl = 0)
    {
        \apcu_store($key, $value, $ttl);
    }

    /**
     * Deletes a cache entry, from its key
     *
     * @param string $key the key of the cache entry
     * @return void
     */
    public function delete($key)
    {
        \apcu_delete($key);
    }

    /**
     * Clears the whole cache
     *
     * @return void
     */
    public function clear()
    {
        \apcu_clear_cache();
    }
}
