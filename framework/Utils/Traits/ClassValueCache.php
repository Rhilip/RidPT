<?php
/**
 * FIXME remove to another namespace
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 7/20/2019
 * Time: 10:31 PM
 */

namespace Rid\Utils\Traits;

trait ClassValueCache
{
    protected function getCacheNameSpace(): string
    {
        return 'Cache:default';
    }

    // Get from class, redis cache, generate closure (may database) and then cache it in class and redis cache
    final protected function getCacheValue(string $key, callable $closure, int $ttl = null)
    {
        if (!isset($this->$key)) {
            /** @var array $key_from_cache */
            $key_from_cache = app()->redis->hGet($this->getCacheNameSpace(), $key);
            if (false === $key_from_cache || ($key_from_cache['expire'] ?? -1) > time()) {
                $this->$key = $closure();
                $cache = ['data' => $this->$key];
                if (!is_null($ttl)) {
                    $cache['expire'] = time() + $ttl;
                }
                app()->redis->hSet($this->getCacheNameSpace(), $key, $cache);
            } else {
                $this->$key = $key_from_cache['data'];
            }
        }
        return $this->$key;
    }

    final protected function removeCacheValue($key)
    {
        unset($this->$key);
        app()->redis->hDel($this->getCacheNameSpace(), $key);
    }
}
