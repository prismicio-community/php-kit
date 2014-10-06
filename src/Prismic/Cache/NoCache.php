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
 * A cache implementation that doesn't cache anything; to be passed
 * as the $cache parameter of Prismic\Api::get when you don't want any caching.
 * This documentation right here introduces what the functions are supposed
 * to do if there is a cache involved, even though in this class in particular,
 * they all simply do nothing and return false.
 */
class NoCache implements CacheInterface
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
        return false;
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
        return null;
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
    public function set($key, $value, $ttl = null)
    {
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
    }
}
