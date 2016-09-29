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
 * it is based on APCU, and therefore requires APCU to be installed on the server.
 */

$CACHE_FOLDER = "/tmp/cache";

class FileCache implements CacheInterface
{
    private $cache;

    public function __construct() {
        $res = true;
        if (!file_exists($CACHE_FOLDER)) {
            $res = mkdir($CACHE_FOLDER, 0700);
        }
        if($res) {
            $this->cache = new \Doctrine\Common\Cache\PhpFileCache($CACHE_FOLDER);
        } else {
            die("Failed to create cache folder");
        }
    }

    /**
     * Tests whether the cache has a value for a particular key
     *
     * @param string $key the key of the cache entry
     * @return boolean true if the cache has a value for this key, otherwise false
     */
    public function has($key)
    {
        return $this->cache->doContains($key);
    }

    /**
     * Returns the value of a cache entry from its key
     *
     * @param  string    $key the key of the cache entry
     * @return mixed the value of the entry, as it was passed to CacheInterface::set, null if not present in cache
     */
    public function get($key)
    {
        $res = $this->cache->doFetch($key);
        if ($res == false) {
          return null;
        } else {
          return $res;
        }
    }

    /**
     * Stores a new cache entry
     *
     * @param string    $key   the key of the cache entry
     * @param \stdClass $value the value of the entry
     * @param int       $ttl   the time until this cache entry expires
     * @return void
     */
    public function set($key, $value, $ttl = 0)
    {
        $this->cache->doSave($key, $value, $ttl);
    }

    /**
     * Deletes a cache entry, from its key
     *
     * @param string $key the key of the cache entry
     * @return void
     */
    public function delete($key)
    {
         $this->cache->doDelete($key);
    }

    /**
     * Clears the whole cache
     *
     * @return void
     */
    public function clear()
    {
        $this->cache->doFlush();
    }
}
