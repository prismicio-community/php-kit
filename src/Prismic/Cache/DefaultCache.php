<?php
declare(strict_types=1);

namespace Prismic\Cache;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\ApcuAdapter;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use function extension_loaded;
use function ini_get;
use function str_replace;

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
        return new ApcuAdapter(str_replace('\\', '', __NAMESPACE__));
    }

    public static function getArrayCache() : CacheItemPoolInterface
    {
        return new ArrayAdapter();
    }
}
