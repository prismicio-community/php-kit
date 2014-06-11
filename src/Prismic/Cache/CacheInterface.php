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
 * This is the interface you're supposed to implement if you want to
 * use your own caching strategy with the kit.
 * The way it works is pretty simple: implement the 4 methods with your
 * implementation, and pass an instance of your class as the $cache parameter
 * in your Prismic\Api::get call.
 * Two implementations are included in the PHP kit out-of-the-box:
 * DefaultCache (which works with APC) and NoCache (which doesn't cache).
 *
 * @api
 */
interface CacheInterface
{
    /**
     * Returns the value of a cache entry from its key
     *
     * @api
     *
     * @param  string    $key  the key of the cache entry
     * @return \stdClass the value of the entry
     */
    public function get($key);

    /**
     * Stores a new cache entry
     *
     * @api
     *
     * @param  string    $key   the key of the cache entry
     * @param  \stdClass $value the value of the entry
     * @param  integer   $ttl   the time until this cache entry expires
     */
    public function set($key, $value, $ttl = 0);

    /**
     * Deletes a cache entry, from its key
     *
     * @api
     *
     * @param  string    $key  the key of the cache entry
     */
    public function delete($key);

    /**
     * Clears the whole cache
     *
     * @api
     */
    public function clear();
}
