<?php
/**
 * This file is part of the Prismic PHP SDK
 *
 * Copyright 2013 Zengularity (http://www.zengularity.com).
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Prismic\Cache;

/**
 * The default implementation that is passed in the Api object when created:
 * it is based on APC, and therefore requires APC to be installed on the server.
 */
class ApcCache implements CacheInterface
{
    /**
     * Tests whether the cache has a value for a particular key
     *
     * @api
     *
     * @param string $key the key of the cache entry
     * @return boolean true if the cache has a value for this key, otherwise false
     */
    public function has($key)
    {
        return \apc_exists($key);
    }

    /**
     * Returns the value of a cache entry from its key
     *
     * @api
     *
     * @param  string    $key the key of the cache entry
     * @return mixed the value of the entry, as it was passed to CacheInterface::set, null if not present in cache
     */
    public function get($key)
    {
        $value = \apc_fetch($key, $success);
        if (!$success) {
            return null;
        }
        return $value;
    }

    /**
     * Stores a new cache entry
     *
     * @api
     *
     * @param string    $key   the key of the cache entry
     * @param \stdClass $value the value of the entry
     * @param integer   $ttl   the time until this cache entry expires
     * @return void
     */
    public function set($key, $value, $ttl = 0)
    {
        \apc_store($key, $value, $ttl);
    }

    /**
     * Deletes a cache entry, from its key
     *
     * @api
     *
     * @param string $key the key of the cache entry
     * @return void
     */
    public function delete($key)
    {
        \apc_delete($key);
    }

    /**
     * Clears the whole cache
     *
     * @api
     *
     * @return void
     */
    public function clear()
    {
        \apc_clear_cache("user");
    }
}
