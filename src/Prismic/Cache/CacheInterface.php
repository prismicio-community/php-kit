<?php

namespace Prismic\Cache;

/**
 * This is the interface you're supposed to implement if you want to
 * use your own caching strategy with the kit.
 *
 * The way it works is pretty simple: implement the methods with your
 * implementation, and pass an instance of your class as the $cache parameter
 * in your Prismic\Api::get call.
 *
 * When writing your implementation be sure to check if your cache backend has a
 * maximum key length. If so you will need to perform an operation on the key
 * passed to the interface methods to limit the length, such as hashing it,
 * since there is no guarantee that the passed keys will be any particular
 * length.
 *
 * Two implementations are included in the PHP kit out-of-the-box:
 * ApcCache (which works with APC) and NoCache (which doesn't cache).
 */
interface CacheInterface
{
    /*
     *
     * @param string $key the key of the cache entry
     * @return boolean true if the cache has a value for this key, otherwise false
     */
    public function has($key);

    /**
     * Returns the value of a cache entry from its key
     *
     *
     * @param  string    $key the key of the cache entry
     * @return mixed the value of the entry, as it was passed to CacheInterface::set, null if not present in cache
     */
    public function get($key);

    /**
     * Stores a new cache entry
     *
     * @param string    $key   the key of the cache entry
     * @param mixed     $value the value of the entry
     * @param int       $ttl   the time (in seconds) until this cache entry expires
     * @return void
     */
    public function set($key, $value, $ttl = 0);

    /**
     * Deletes a cache entry, from its key
     *
     * @param string $key the key of the cache entry
     * @return void
     */
    public function delete($key);

    /**
     * Clears the whole cache
     *
     * @return void
     */
    public function clear();
}
