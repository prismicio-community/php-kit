<?php


namespace Prismic\Cache;


class RedisCache implements CacheInterface
{
    private $objRedis;

    //---------------Config Vars-----------------
    private $intMaxTTL = 300; //Seconds
    private $strHost = 'localhost';

    function __construct() {
       $this->objRedis = new \Redis();
       $this->objRedis->connect($this->strHost);
       $this->objRedis->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_PHP);
    }

    /**
     * Returns the value of a cache entry from its key
     *
     * @api
     *
     * @param  string    $key the key of the cache entry
     * @return \stdClass the value of the entry
     */
    public function get($key)
    {
      $strNewKey = $key;
      return $this->objRedis->get($strNewKey);

    }

    /**
     * Stores a new cache entry
     *
     * @api
     *
     * @param string    $key   the key of the cache entry
     * @param \stdClass $value the value of the entry
     * @param integer   $ttl   the time until this cache entry expires
     */
    public function set($key, $value, $ttl = 500)
    {
      $strNewKey = $key;

      $intNewTTL = $ttl;
      //Limit max TTL
      if($this->intMaxTTL > 0 && $intNewTTL > $this->intMaxTTL) {
        $intNewTTL = $this->intMaxTTL;
      }
      return $this->objRedis->set($strNewKey, $value, $intNewTTL);
    }

    /**
     * Deletes a cache entry, from its key
     *
     * @api
     *
     * @param string $key the key of the cache entry
     */
    public function delete($key)
    {
        return $this->objRedis->del($key);
    }

    /**
     * Clears the whole cache
     *
     * @api
     */
    public function clear()
    {
        return $this->objRedis->flushDB();
    }
}