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

    // Get from class, redis cache, then generate closure (may database)
    final protected function getCacheValue($key, $closure)
    {
        if (is_null($this->$key)) {
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
        $value = app()->redis->get(static::getStaticCacheNameSpace() . ':' . $key);   // FIXME use zset
        if (false === $value) {
            $value = $closure();
            app()->redis->set(static::getStaticCacheNameSpace() . ':' . $key, $value, $ttl);
        }
        return $value;
    }
}
