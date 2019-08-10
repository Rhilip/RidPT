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
    static $_StaticCacheValue = [];

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
        $timenow = time();
        if (array_key_exists($key, static::$_StaticCacheValue)) {
            if ($timenow > static::$_StaticCacheValue[$key . ':expired_at']) {
                static::cleanStaticCacheValue($key);
            } else {
                return static::$_StaticCacheValue[$key];
            }
        }

        $value = app()->redis->get(static::getStaticCacheNameSpace() . ':' . $key);
        if (false === $value) {
            $value = $closure();
            app()->redis->set(static::getStaticCacheNameSpace() . ':' . $key, $value, $ttl);
        }

        static::setStaticCacheValue($key, $value, $ttl, false);
        return $value;
    }

    final protected static function setStaticCacheValue($key, $value, $ttl, $clean_first = true)
    {
        $timenow = time();
        if ($clean_first) static::cleanStaticCacheValue($key);
        static::$_StaticCacheValue[$key] = $value;
        static::$_StaticCacheValue[$key . ':expired_at'] = $timenow + $ttl;
    }

    final protected static function cleanStaticCacheValue($key)
    {
        if (array_key_exists($key, static::$_StaticCacheValue)) {
            unset(static::$_StaticCacheValue[$key]);
            unset(static::$_StaticCacheValue[$key . ':expired_at']);
            app()->redis->del(static::getStaticCacheNameSpace() . ':' . $key);
        }
    }
}
