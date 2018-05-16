<?php
declare(strict_types=1);

namespace Prismic\Cache;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\ApcuAdapter;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class DefaultCache
{

    public static function factory() : CacheItemPoolInterface
    {
        if (extension_loaded('apc') && ini_get('apc.enabled')) {
            return static::getApcCache();
        }
        return static::getArrayCache();
    }

    public static function getApcCache() : CacheItemPoolInterface
    {
        return new ApcuAdapter(__NAMESPACE__);
    }

    public static function getArrayCache() : CacheItemPoolInterface
    {
        return new ArrayAdapter();
    }
}
