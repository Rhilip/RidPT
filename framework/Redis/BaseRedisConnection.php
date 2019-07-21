<?php

namespace Rid\Redis;

use Rid\Base\Component;

/**
 * BaseRedis组件
 *
 * @method bool psetex($key, $ttl, $value)
 * @method array|bool sScan($key, $iterator, $pattern = '', $count = 0)
 * @method array|bool scan(&$iterator, $pattern = null, $count = 0)
 * @method array|bool zScan($key, $iterator, $pattern = '', $count = 0)
 * @method array hScan($key, $iterator, $pattern = '', $count = 0)
 * @method mixed client($command, $arg = '')
 * @method mixed slowlog($command)
 * @method bool open($host, $port = 6379, $timeout = 0.0, $retry_interval = 0)
 * @method popen($host, $port = 6379, $timeout = 0.0, $persistent_id = null)
 * @method close()
 * @method bool setOption($name, $value)
 * @method int getOption($name)
 * @method string ping()
 * @method string|bool get($key)
 * @method bool set($key, $value, $timeout = 0)
 * @method bool setex($key, $ttl, $value)
 * @method bool setnx($key, $value)
 * @method int del($key1, $key2 = null, $key3 = null)
 * @method int delete($key1, $key2 = null, $key3 = null)
 * @method \Redis multi($mode = \Redis::MULTI)
 * @method array exec()
 * @method discard()
 * @method watch($key)
 * @method unwatch()
 * @method subscribe($channels, $callback)
 * @method psubscribe($patterns, $callback)
 * @method int publish($channel, $message)
 * @method array|int pubsub($keyword, $argument)
 * @method bool exists($key)
 * @method int incr($key)
 * @method float incrByFloat($key, $increment)
 * @method int incrBy($key, $value)
 * @method int decr($key)
 * @method int decrBy($key, $value)
 * @method array getMultiple(array $keys)
 * @method int|bool lPush($key, $value1, $value2 = null, $valueN = null)
 * @method int|bool rPush($key, $value1, $value2 = null, $valueN = null)
 * @method int lPushx($key, $value)
 * @method int rPushx($key, $value)
 * @method string lPop($key)
 * @method string rPop($key)
 * @method array blPop(array $keys, $timeout)
 * @method array brPop(array $keys, $timeout)
 * @method int lLen($key)
 * @method lSize($key)
 * @method lIndex($key, $index)
 * @method lGet($key, $index)
 * @method lSet($key, $index, $value)
 * @method array lRange($key, $start, $end)
 * @method lGetRange($key, $start, $end)
 * @method array lTrim($key, $start, $stop)
 * @method listTrim($key, $start, $stop)
 * @method int lRem($key, $value, $count)
 * @method lRemove($key, $value, $count)
 * @method int lInsert($key, $position, $pivot, $value)
 * @method int sAdd($key, $value1, $value2 = null, $valueN = null)
 * @method sAddArray($key, array $values)
 * @method int sRem($key, $member1, $member2 = null, $memberN = null)
 * @method sRemove($key, $member1, $member2 = null, $memberN = null)
 * @method bool sMove($srcKey, $dstKey, $member)
 * @method bool sIsMember($key, $value)
 * @method sContains($key, $value)
 * @method int sCard($key)
 * @method string sPop($key)
 * @method string|array sRandMember($key, $count = null)
 * @method array sInter($key1, $key2, $keyN = null)
 * @method int sInterStore($dstKey, $key1, $key2, $keyN = null)
 * @method array sUnion($key1, $key2, $keyN = null)
 * @method int sUnionStore($dstKey, $key1, $key2, $keyN = null)
 * @method array sDiff($key1, $key2, $keyN = null)
 * @method int sDiffStore($dstKey, $key1, $key2, $keyN = null)
 * @method array sMembers($key)
 * @method sGetMembers($key)
 * @method string getSet($key, $value)
 * @method string randomKey()
 * @method bool select($dbindex)
 * @method bool move($key, $dbindex)
 * @method bool rename($srcKey, $dstKey)
 * @method renameKey($srcKey, $dstKey)
 * @method bool renameNx($srcKey, $dstKey)
 * @method bool expire($key, $ttl)
 * @method bool pExpire($key, $ttl)
 * @method setTimeout($key, $ttl)
 * @method bool expireAt($key, $timestamp)
 * @method bool pExpireAt($key, $timestamp)
 * @method array keys($pattern)
 * @method getKeys($pattern)
 * @method int dbSize()
 * @method bool auth($password)
 * @method bool bgrewriteaof()
 * @method bool slaveof($host = '127.0.0.1', $port = 6379)
 * @method string object($string = '', $key = '')
 * @method bool save()
 * @method bool bgsave()
 * @method int lastSave()
 * @method int wait($numSlaves, $timeout)
 * @method int type($key)
 * @method int append($key, $value)
 * @method string getRange($key, $start, $end)
 * @method substr($key, $start, $end)
 * @method string setRange($key, $offset, $value)
 * @method int strlen($key)
 * @method int bitpos($key, $bit, $start = 0, $end = null)
 * @method int getBit($key, $offset)
 * @method int setBit($key, $offset, $value)
 * @method int bitCount($key)
 * @method int bitOp($operation, $retKey, ...$keys)
 * @method bool flushDB()
 * @method bool flushAll()
 * @method array sort($key, $option = null)
 * @method array|string info($option = null)
 * @method bool resetStat()
 * @method int ttl($key)
 * @method int pttl($key)
 * @method bool persist($key)
 * @method bool mset(array $array)
 * @method array mget(array $array)
 * @method int msetnx(array $array)
 * @method string rpoplpush($srcKey, $dstKey)
 * @method string brpoplpush($srcKey, $dstKey, $timeout)
 * @method int zAdd($key, $score1, $value1, $score2 = null, $value2 = null, $scoreN = null, $valueN = null)
 * @method array zRange($key, $start, $end, $withscores = null)
 * @method int zRem($key, $member1, $member2 = null, $memberN = null)
 * @method int zDelete($key, $member1, $member2 = null, $memberN = null)
 * @method array zRevRange($key, $start, $end, $withscore = null)
 * @method array zRangeByScore($key, $start, $end, array $options = [])
 * @method array zRevRangeByScore($key, $start, $end, array $options = [])
 * @method array zRangeByLex($key, $min, $max, $offset = null, $limit = null)
 * @method array zRevRangeByLex($key, $min, $max, $offset = null, $limit = null)
 * @method int zCount($key, $start, $end)
 * @method int zRemRangeByScore($key, $start, $end)
 * @method zDeleteRangeByScore($key, $start, $end)
 * @method int zRemRangeByRank($key, $start, $end)
 * @method zDeleteRangeByRank($key, $start, $end)
 * @method int zCard($key)
 * @method zSize($key)
 * @method float zScore($key, $member)
 * @method int zRank($key, $member)
 * @method int zRevRank($key, $member)
 * @method float zIncrBy($key, $value, $member)
 * @method int zUnion($Output, $ZSetKeys, array $Weights = null, $aggregateFunction = 'SUM')
 * @method int zInter($Output, $ZSetKeys, array $Weights = null, $aggregateFunction = 'SUM')
 * @method int|bool hSet($key, $hashKey, $value)
 * @method bool hSetNx($key, $hashKey, $value)
 * @method string|bool hGet($key, $hashKey)
 * @method int hLen($key)
 * @method int|bool hDel($key, $hashKey1, $hashKey2 = null, $hashKeyN = null)
 * @method array hKeys($key)
 * @method array hVals($key)
 * @method array hGetAll($key)
 * @method bool hExists($key, $hashKey)
 * @method int hIncrBy($key, $hashKey, $value)
 * @method float hIncrByFloat($key, $field, $increment)
 * @method bool hMset($key, $hashKeys)
 * @method array hMGet($key, $hashKeys)
 * @method array config($operation, $key, $value)
 * @method mixed evaluate($script, $args = [], $numKeys = 0)
 * @method mixed evalSha($scriptSha, $args = [], $numKeys = 0)
 * @method evaluateSha($scriptSha, $args = [], $numKeys = 0)
 * @method mixed script($command, $script)
 * @method string|null getLastError()
 * @method bool clearLastError()
 * @method string dump($key)
 * @method bool restore($key, $ttl, $value)
 * @method bool migrate($host, $port, $key, $db, $timeout, $copy = false, $replace = false)
 * @method array time()
 * @method bool pfAdd($key, array $elements)
 * @method int pfCount($key)
 * @method bool pfMerge($destkey, array $sourcekeys)
 * @method mixed rawCommand($command, $arguments)
 * @method int getMode()
 */
class BaseRedisConnection extends Component
{

    // 主机
    public $host = '';

    // 端口
    public $port = '';

    // 数据库
    public $database = '';

    // 密码
    public $password = '';

    // 驱动连接选项
    public $driverOptions = [];

    // 默认驱动连接选项
    protected $_defaultDriverOptions = [
        \Redis::OPT_SERIALIZER => \Redis::SERIALIZER_PHP,  // 默认做序列化
        \Redis::OPT_PREFIX => "",
    ];

    // 驱动连接选项
    protected $_driverOptions = [];

    // redis对象
    /** @var \Redis */
    protected $_redis;

    protected $_calledData = [];

    // 初始化事件
    public function onInitialize()
    {
        parent::onInitialize();
        $this->_driverOptions = $this->driverOptions + $this->_defaultDriverOptions;  // 设置驱动连接选项
    }

    public function onRequestAfter()
    {
        $this->_calledData = [];
    }

    // 创建连接
    protected function createConnection()
    {
        $redis = new \Redis();
        // connect 这里如果设置timeout，是全局有效的，执行brPop时会受影响
        if (!$redis->connect($this->host, $this->port)) {
            throw new \Rid\Exceptions\ConnectionException('redis connection failed.');
        }
        $redis->auth($this->password);
        $redis->select($this->database);

        foreach ($this->_driverOptions as $key => $value) {
            $redis->setOption($key, $value);
        }

        return $redis;
    }

    // 连接
    protected function connect()
    {
        $this->_redis = $this->createConnection();
    }

    // 关闭连接
    public function disconnect()
    {
        $this->_redis = null;
    }

    // 自动连接
    protected function autoConnect()
    {
        if (!isset($this->_redis)) {
            $this->connect();
        }
    }

    // 执行命令
    public function __call($name, $arguments)
    {

        $this->autoConnect();   // 自动连接

        $arg_text = '';
        foreach ($arguments as $arg) {
            if (!is_string($arg)) $arg = '[Array]';
            $arg_text .= ' ' . $arg;
        }

        $calling = $name . ($arguments ? ' ' . $arg_text : '');
        if (isset($this->_calledData[$calling])) {
            $this->_calledData[$calling] += 1;
        } else {
            $this->_calledData[$calling] = 1;
        }

        return call_user_func_array([$this->_redis, $name], $arguments);  // 执行命令
    }

    // 扩展方法
    public function typeof($key): ?string
    {
        switch ($this->type($key)) {
            case \Redis::REDIS_STRING :
                return "String";
            case \Redis::REDIS_SET :
                return "Set";
            case \Redis::REDIS_LIST :
                return "List";
            case \Redis::REDIS_ZSET :
                return "Sorted Set";
            case \Redis::REDIS_HASH :
                return "Hash";
            case \Redis::REDIS_NOT_FOUND :
            default:
                return "Not Found";
        }
    }

    public function mutiDelete($pattern) {
        return $this->del($this->keys($pattern));
    }

    public function getCalledData()
    {
        return $this->_calledData;
    }

    /**
     * @return \Redis
     */
    public function getRedis(): \Redis
    {
        return $this->_redis;
    }
}
