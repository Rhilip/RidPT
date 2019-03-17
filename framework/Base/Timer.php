<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2018/12/30
 * Time: 15:24
 */

namespace Rid\Base;


/**
 * Class Timer
 * @package Rid\Core
 */
class Timer implements StaticInstanceInterface
{
    use StaticInstanceTrait;

    const AFTER = 'after';
    const TICK = 'tick';

    /**
     * 定时器ID
     * @var int
     */
    protected $_timerId;
    /**
     * 在指定的时间后执行函数
     * 一次性执行
     * @param int $msec
     * @param callable $callback
     * @return int
     */
    public function after(int $msec, callable $callback)
    {
        // 清除旧定时器
        $this->clear();
        // 设置定时器
        $timerId = swoole_timer_after($msec, function () use ($callback) {
            // 执行闭包
            try {
                call_user_func($callback);
            } catch (\Throwable $e) {
                // 输出错误
                \Rid::app()->error->handleException($e);
            }
        });
        // 保存id
        $this->_timerId = $timerId;
        // 返回
        return $timerId;
    }
    /**
     * 设置一个间隔时钟定时器
     * 持续触发
     * @param int $msec
     * @param callable $callback
     * @return int
     */
    public function tick(int $msec, callable $callback)
    {
        // 清除旧定时器
        $this->clear();
        // 设置定时器
        $timerId = swoole_timer_tick($msec, function () use ($callback) {
            // 执行闭包
            try {
                call_user_func($callback);
            } catch (\Throwable $e) {
                // 输出错误
                \Rid::app()->error->handleException($e);
            }
        });
        // 保存id
        $this->_timerId = $timerId;
        // 返回
        return $timerId;
    }
    /**
     * 清除旧定时器
     * @return bool
     */
    public function clear()
    {
        if (isset($this->_timerId)) {
            return swoole_timer_clear($this->_timerId);
        }
        return false;
    }

    public function run($config)
    {
        $type = $config['type'];
        $msec = $config['msec'];
        $callback = $config['callback'];

        println('New Timer '. self::class .' added. (Type: ' . $type . ', msec: ' . $msec . ', callback function: ' . $callback . ')');
        $this->{$type}($msec, [$this, $callback]);
    }
}
