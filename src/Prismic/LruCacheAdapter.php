<?php

/*
 * This file is part of the Prismic PHP SDK
 *
 * Copyright 2013 Zengularity (http://www.zengularity.com).
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Prismic;

use Guzzle\Cache\CacheAdapterInterface;

class LruCacheAdapter implements CacheAdapterInterface
{

	protected $maxSize;
	protected $cache;

	public function __construct($maxSize) {
		$this->maxSize = $maxSize;
		$this->cache = array();
	}

    public function contains($id, array $options = null)
    {
    	// check if contained
    	return array_key_exists($id, $this->cache);
    }

    public function delete($id, array $options = null)
    {
    	$existed = $this->contains($id);
    	unset($this->cache[$id]);
    	return $existed;
    }

    public function fetch($id, array $options = null)
    {
    	if ($this->contains($id)) {
    		// fetch data
    		$data = $this->cache[$id];
    		// delete entry to put it back at the end of the array (and let it live longer)
    		unset($this->cache[$id]);
    		$this->cache[$id] = $data;
    		// return the data
    		return $data;
    	}
    	else {
    		return null;
    	}
    }

    public function save($id, $data, $lifeTime = false, array $options = null)
    {
    	// save value at the end of the array
    	$this->cache[$id] = $data;
    	// checks if size too big --> shift the array
    	if ($this->size() > $this->maxSize) {
    		array_shift($this->cache);
    	}
    }

    public function size() {
    	return count($this->cache);
    }

    public function toArray() {
    	return $this->cache;
    }

}