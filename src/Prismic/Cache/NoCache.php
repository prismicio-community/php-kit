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
     * Returns the value of a cache entry from its key
     *
     * @api
     *
     * @param  string    $key  the key of the cache entry
     * @return \stdClass the value of the entry
     */
    public function get($key)
    {
        return false;
    }

    /**
     * Stores a new cache entry
     *
     * @api
     *
     * @param  string    $key   the key of the cache entry
     * @param  \stdClass $value the value of the entry
     * @param  integer   $ttl   the time until this cache entry expires
     */
    public function set($key, $value, $ttl = null)
    {
        return false;
    }

    /**
     * Deletes a cache entry, from its key
     *
     * @api
     *
     * @param  string    $key  the key of the cache entry
     */
    public function delete($key)
    {
        return false;
    }

    /**
     * Clears the whole cache
     *
     * @api
     */
    public function clear()
    {
        return false;
    }
}
