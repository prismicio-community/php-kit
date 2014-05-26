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

interface CacheInterface
{
    public function get($key);

    public function set($key, $value, $ttl = 0);

    public function delete($key);

    public function clear();
}
