<?php

namespace Rid\Redis;

use Rid\Base\Component;

/**
 * BaseRedis组件
 * @link https://github.com/ukko/phpredis-phpdoc/blob/master/src/Redis.php
 *
 * @method bool isConnected()
 * @method string|bool getHost()
 * @method int|bool getPort()
 * @method int|bool getDbNum()
 * @method float|bool getTimeout()
 * @method float|bool getReadTimeout()
 * @method string|null|bool getPersistentID()
 * @method string|null|bool getAuth()
 * @method bool pconnect(string $host, int $port = 6379, float $timeout = 0.0, string $persistentId = null, int $retryInterval = 0, float $readTimeout = 0.0)
 * @method bool popen(string $host, int $port = 6379, float $timeout = 0.0, string $persistentId = '', int $retryInterval = 0, float $readTimeout = 0.0)
 * @method bool close()
 * @method bool swapdb(int $db1, int $db2)
 * @method bool setOption(int $option, mixed $value)
 * @method mixed|null getOption(int $option)
 * @method string ping()
 * @method string echo (string $message)
 * @method string|mixed|bool get(string $key)
 * @method bool set(string $key, string|mixed $value, int|array $timeout = null)
 * @method bool setex(string $key, int $ttl, string|mixed $value)
 * @method bool psetex(string $key, int $ttl, string|mixed $value)
 * @method bool setnx(string $key, string|mixed $value)
 * @method int del(int|string|array $key1, int|string ...$otherKeys)
 * @method int unlink(string|string[] $key1, string $key2 = null, string $key3 = null)
 * @method \Redis multi(int $mode = \Redis::MULTI)
 * @method void|array exec()
 * @method discard()
 * @method void watch(string|string[] $key)
 * @method unwatch()
 * @method mixed|null subscribe(string[] $channels, string|array $callback)
 * @method psubscribe(array $patterns, string|array $callback)
 * @method int publish(string $channel, string $message)
 * @method array|int pubsub(string $keyword, string|array $argument)
 * @method unsubscribe(array $channels = null)
 * @method punsubscribe(array $patterns = null)
 * @method int|bool exists(string|string[] $key)
 * @method int  incr(string $key)
 * @method float incrByFloat(string $key, float $increment)
 * @method int incrBy(string $key, int $value)
 * @method int decr(string $key)
 * @method int decrBy(string $key, int $value)
 * @method int|bool lPush(string $key, string|mixed ...$value1)
 * @method int|bool rPush(string $key, string|mixed ...$value1)
 * @method int|bool lPushx(string $key, string|mixed $value)
 * @method int|bool rPushx(string $key, string|mixed $value)
 * @method mixed|bool lPop(string $key)
 * @method mixed|bool rPop(string $key)
 * @method array blPop(string|string[] $keys, int $timeout)
 * @method array brPop(string|string[] $keys, int $timeout)
 * @method int|bool lLen(string $key)
 * @method int lSize(string $key)
 * @method mixed|bool lIndex(string $key, int $index)
 * @method mixed|bool lGet(string $key, int $index)
 * @method bool lSet(string $key, int $index, string $value)
 * @method array lRange(string $key, int $start, int $end)
 * @method array|bool lTrim(string $key, int $start, int $stop)
 * @method int|bool lRem(string $key, string $value, int $count)
 * @method int lInsert(string $key, int $position, string $pivot, string|mixed $value)
 * @method int|bool sAdd(string $key, string|mixed ...$value1)
 * @method int sRem(string $key, string|mixed ...$member1)
 * @method bool sMove(string $srcKey, string $dstKey, string|mixed $member)
 * @method bool sIsMember(string $key, string|mixed $value)
 * @method int sCard(string $key)
 * @method string|mixed|array|bool sPop(string $key, int $count = 1)
 * @method string|mixed|array|bool sRandMember(string $key, int $count = 1)
 * @method array sInter(string $key1, string ...$otherKeys)
 * @method int|bool sInterStore(string $dstKey, string $key1, string ...$otherKeys)
 * @method array sUnion(string $key1, string ...$otherKeys)
 * @method int sUnionStore(string $dstKey, string $key1, string ...$otherKeys)
 * @method array sDiff(string $key1, string ...$otherKeys)
 * @method int|bool sDiffStore(string $dstKey, string $key1, string  ...$otherKeys)
 * @method array sMembers(string $key)
 * @method array|bool sScan(string $key, int &$iterator, string $pattern = null, int $count = 0)
 * @method string|mixed getSet(string $key, string|mixed $value)
 * @method string randomKey()
 * @method bool select(int $dbIndex)
 * @method bool move(string $key, int $dbIndex)
 * @method bool rename(string $srcKey, string $dstKey)
 * @method bool renameNx(string $srcKey, string $dstKey)
 * @method bool expire(string $key, int $ttl)
 * @method bool pExpire(string $key, int $ttl)
 * @method bool expireAt(string $key, int $timestamp)
 * @method bool pExpireAt(string $key, int $timestamp)
 * @method array keys(string $pattern)
 * @method int dbSize()
 * @method bool auth(string $password)
 * @method bool bgrewriteaof()
 * @method bool slaveof(string $host = '127.0.0.1', int $port = 6379)
 * @method mixed slowLog(string $operation, int $length = null)
 * @method string|int|bool object(string $string = '', string $key = '')
 * @method bool save()
 * @method bool bgsave()
 * @method int lastSave()
 * @method int wait(int $numSlaves, int $timeout)
 * @method int type(string $key)
 * @method int append(string $key, string|mixed $value)
 * @method string getRange(string $key, int $start, int $end)
 * @method string substr(string $key, int $start, int $end)
 * @method int setRange(string $key, int $offset, string $value)
 * @method int strlen(string $key)
 * @method int bitpos(string $key, int $bit, int $start = 0, int $end = null)
 * @method int getBit(string $key, int $offset)
 * @method int setBit(string $key, int $offset, bool|int $value)
 * @method int bitCount(string $key)
 * @method int bitOp(string $operation, string $retKey, string $key1, string ...$otherKeys)
 * @method bool flushDB()
 * @method bool flushAll()
 * @method array sort(string $key, array $option = null)
 * @method string info(string $option = null)
 * @method bool resetStat()
 * @method int|bool ttl(string $key)
 * @method int|bool pttl(string $key)
 * @method bool persist(string $key)
 * @method bool mset(array $array)
 * @method array mget(array $array)
 * @method int msetnx(array $array)
 * @method string|mixed|bool rpoplpush(string $srcKey, string $dstKey)
 * @method string|mixed|bool brpoplpush(string $srcKey, string $dstKey, int $timeout)
 * @method int zAdd(string $key, float $score1, string|mixed $value1, float $score2 = null, string|mixed $value2 = null, float $scoreN = null, string|mixed $valueN = null)
 * @method array zRange(string $key, int $start, int $end, bool $withscores = null)
 * @method int zRem(string $key, string|mixed $member1, string|mixed ...$otherMembers)
 * @method array zRevRange(string $key, int $start, int $end, bool $withscore = null)
 * @method array zRangeByScore(string $key, int $start, int $end, array $options = array())
 * @method array zRevRangeByScore(string $key, int $start, int $end, array $options = array())
 * @method array|bool zRangeByLex(string $key, int $min, int $max, int $offset = null, int $limit = null)
 * @method array|bool zRevRangeByLex(string $key, int $min, int $max, int $offset = null, int $limit = null)
 * @method int zCount(string $key, string $start, string $end)
 * @method int zRemRangeByScore(string $key, float|string $start, float|string $end)
 * @method int zRemRangeByRank(string $key, int $start, int $end)
 * @method int zCard(string $key)
 * @method float|bool zScore(string $key, string|mixed $member)
 * @method int|bool zRank(string $key, string|mixed $member)
 * @method int|bool zRevRank(string $key, string|mixed $member)
 * @method float zIncrBy(string $key, float $value, string $member)
 * @method int zUnionStore(string $output, array $zSetKeys, array $weights = null, string $aggregateFunction = 'SUM')
 * @method int zInterStore(string $output, array $zSetKeys, array $weights = null, string $aggregateFunction = 'SUM')
 * @method array|bool zScan(string $key, int &$iterator, string $pattern = null, int $count = 0)
 * @method array bzPopMax(string|array $key1, string|array $key2, int $timeout)
 * @method array bzPopMin(string|array $key1, string|array $key2, int $timeout)
 * @method array zPopMax(string $key, int $count = 1)
 * @method array zPopMin(string $key, int $count = 1)
 * @method int|bool hSet(string $key, string $hashKey, string|int $value)
 * @method bool hSetNx(string $key, string $hashKey, string $value)
 * @method string hGet(string $key, string $hashKey)
 * @method int|bool hLen(string $key)
 * @method int|bool hDel(string $key, string $hashKey1, string ...$otherHashKeys)
 * @method array hKeys(string $key)
 * @method array hVals(string $key)
 * @method array hGetAll(string $key)
 * @method bool hExists(string $key, string $hashKey)
 * @method int hIncrBy(string $key, string $hashKey, int $value)
 * @method float hIncrByFloat(string $key, string $field, float $increment)
 * @method bool hMSet(string $key, array $hashKeys)
 * @method array hMGet(string $key, array $hashKeys)
 * @method array hScan(string $key, int &$iterator, string $pattern = null, int $count = 0)
 * @method int hStrLen(string $key, string $field)
 * @method int geoadd(string $key, float $longitude, float $latitude, string $member)
 * @method array geohash(string $key, string ...$member)
 * @method array geopos(string $key, string $member)
 * @method float geodist(string $key, string $member1, string $member2, string $unit = null)
 * @method georadius($key, $longitude, $latitude, $radius, $unit, array|null $options = null)
 * @method georadiusbymember(string $key, string $member, $radius, $units, array|null $options = null)
 * @method array config(string $operation, string $key, string|mixed $value)
 * @method mixed eval(string $script, array $args = array(), int $numKeys = 0)
 * @method mixed evalSha(string $scriptSha, array $args = array(), int $numKeys = 0)
 * @method mixed script(string $command, string $script)
 * @method string|null getLastError()
 * @method bool clearLastError()
 * @method mixed client(string $command, string $value = '')
 * @method string|bool dump(string $key)
 * @method bool restore(string $key, int $ttl, string $value)
 * @method bool migrate(string $host, int $port, string $key, int $db, int $timeout, bool $copy = false, bool $replace = false)
 * @method array time()
 * @method array|bool scan(int &$iterator, string $pattern = null, int $count = 0)
 * @method bool pfAdd(string $key, array $elements)
 * @method int pfCount(string|array $key)
 * @method bool pfMerge(string $destKey, array $sourceKeys)
 * @method mixed rawCommand(string $command, mixed $arguments)
 * @method int getMode()
 * @method int xAck(string $stream, string $group, array $messages)
 * @method string xAdd(string $key, string $id, array $messages, int $maxLen = 0, bool $isApproximate = false)
 * @method array xClaim(string $key, string $group, string $consumer, int $minIdleTime, array $ids, array $options = [])
 * @method int xDel(string $key, array $ids)
 * @method mixed xGroup(string $operation, string $key, string $group, string $msgId = '', bool $mkStream = false)
 * @method mixed xInfo(string $operation, string $stream, string $group)
 * @method int xLen(string $stream)
 * @method array xPending(string $stream, string $group, string $start = null, string $end = null, int $count = null, string $consumer = null)
 * @method array xRange(string $stream, string $start, string $end, int $count = null)
 * @method array xRead(array $streams, int|string $count = null, int|string $block = null)
 * @method array xReadGroup(string $group, string $consumer, array $streams, int|null $count = null, int|null $block = null)
 * @method array xRevRange(string $stream, string $end, string $start, int $count = null)
 * @method int xTrim(string $stream, int $maxLen, bool $isApproximate)
 * @method int|bool sAddArray(string $key, array $values)
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
        \Redis::OPT_PREFIX => '',
    ];

    // 驱动连接选项
    protected $_driverOptions = [];

    // redis对象
    /** @var \Redis */
    protected $_redis;

    protected $_recordData = true;
    protected $_calledData = [];

    // 初始化事件
    public function onInitialize()
    {
        parent::onInitialize();
        $this->_driverOptions = $this->driverOptions + $this->_defaultDriverOptions;  // 设置驱动连接选项
    }

    public function onRequestBefore()
    {
        parent::onRequestBefore();
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

        if ($this->_recordData) {
            $arg_text = '';
            foreach ($arguments as $arg) {
                if (!is_string($arg)) {
                    $arg = '[Array]';
                }
                $arg_text .= ' ' . $arg;
            }

            $calling = $name . ($arguments ? ' ' . $arg_text : '');
            if (isset($this->_calledData[$calling])) {
                $this->_calledData[$calling] += 1;
            } else {
                $this->_calledData[$calling] = 1;
            }
        }

        return call_user_func_array([$this->_redis, $name], $arguments);  // 执行命令
    }

    public function multiDelete($pattern)
    {
        return $this->del($this->keys($pattern));
    }

    public function getCalledData()
    {
        return $this->_calledData;
    }

    /**
     * @param bool $recordData
     */
    public function setRecordData(bool $recordData): void
    {
        $this->_recordData = $recordData;
    }

    /**
     * @return \Redis
     */
    public function getRedis(): \Redis
    {
        return $this->_redis;
    }
}
