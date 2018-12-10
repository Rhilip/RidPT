<?php

namespace mix\facades;

use mix\base\Facade;

/**
 * Redis 门面类
 * @author 刘健 <coder.liu@qq.com>
 *
 * @method disconnect() static
 * @method select($index) static
 * @method set($key, $value) static
 * @method setex($key, $seconds, $value) static
 * @method setnx($key, $value) static
 * @method get($key) static
 * @method del($key) static
 * @method hmset($key, $array) static
 * @method hmget($key, $array) static
 * @method hgetall($key) static
 * @method hlen($key) static
 * @method hset($key, $field, $value) static
 * @method hsetnx($key, $field, $value) static
 * @method hget($key, $field) static
 * @method lpush($key, $value) static
 * @method rpop($key) static
 * @method brpop($key, $timeout) static
 * @method rpush($key, $value) static
 * @method lpop($key) static
 * @method blpop($key, $timeout) static
 * @method sadd($key, $value) static
 * @method lrange($key, $start, $end) static
 * @method llen($key) static
 * @method subscribe($channel) static
 * @method publish($channel, $message) static
 * @method ttl($key) static
 */
class Redis extends Facade
{

    /**
     * 获取实例
     * @param $name
     * @return \mix\client\Redis
     */
    public static function name($name)
    {
        return static::getInstances()[$name];
    }

    /**
     * 获取实例集合
     * @return array
     */
    public static function getInstances()
    {
        return [
            'default' => \Mix::app()->redis,
        ];
    }

}
