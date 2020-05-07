<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 5/7/2020
 * Time: 2020
 */

declare(strict_types=1);

namespace App\Libraries\Decoda;


use Decoda\Exception\MissingItemException;
use Decoda\Storage\AbstractStorage;
use Rid\Redis\BaseRedisConnection;

class RedisStorage extends AbstractStorage
{

    protected BaseRedisConnection $_redis;

    public function __construct(BaseRedisConnection $redisConnection)
    {
        $this->_redis = $redisConnection;
    }

    /**
     * Return the Redis instance.
     *
     * @return BaseRedisConnection
     */
    public function getRedis() {
        return $this->_redis;
    }

    public function get($key)
    {
        $value = $this->getRedis()->get($key);

        if ($value === false) {
            throw new MissingItemException(sprintf('Item with key %s does not exist', $key));
        }

        return $value;
    }

    public function has($key)
    {
        return $this->getRedis()->exists($key);
    }

    public function remove($key)
    {
        return (bool) $this->getRedis()->del($key);
    }

    public function set($key, $value, $expires)
    {
        return $this->getRedis()->setex($key, (int) $expires - time(), $value); // Redis is TTL
    }
}
