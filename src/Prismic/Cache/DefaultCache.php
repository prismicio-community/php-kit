<?php

/*
 * This file is part of the Prismic PHP SDK
 *
 * Copyright 2013 Zengularity (http://www.zengularity.com).
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Prismic\Cache;

class DefaultCache implements CacheInterface
{
    public function get($key)
    {
        return \apc_fetch($key);
    }

    public function set($key, $value, $ttl = 0)
    {
        return \apc_store($key, $value, $ttl);
    }

    public function delete($key)
    {
        return \apc_delete($key);
    }

    public function clear()
    {
        return \apc_clear_cache("user");
    }
}
