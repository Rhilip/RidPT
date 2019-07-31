<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 7/20/2019
 * Time: 10:31 PM
 */

namespace Rid\Utils;


trait ClassValueCacheUtils
{
    protected static function getStaticCacheNameSpace(): string
    {
        return 'Cache:default_static';
    }

    protected function getCacheNameSpace(): string
    {
        return 'Cache:default';
    }

    // Get from class, redis cache, generate closure (may database) and then cache it in class and redis cache
    final protected function getCacheValue($key, $closure)
    {
        if (!isset($this->$key)) {
            $this->$key = app()->redis->hGet($this->getCacheNameSpace(), $key);
            if (false === $this->$key) {
                $this->$key = $closure();
                app()->redis->hSet($this->getCacheNameSpace(), $key, $this->$key);
            }
        }
        return $this->$key;
    }

    // Get from redis cache, then generate closure (may database)
    final protected static function getStaticCacheValue($key, $closure, $ttl = 86400)
    {
        $value = app()->redis->get(static::getStaticCacheNameSpace() . ':' . $key);
        if (false === $value) {
            $value = $closure();
            app()->redis->set(static::getStaticCacheNameSpace() . ':' . $key, $value, $ttl);
        }
        return $value;
    }
}
