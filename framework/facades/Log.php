<?php

namespace mix\facades;

use mix\base\Facade;

/**
 * Log 门面类
 * @author 刘健 <coder.liu@qq.com>
 *
 * @method emergency($message, array $context = []) static
 * @method alert($message, array $context = []) static
 * @method critical($message, array $context = []) static
 * @method error($message, array $context = []) static
 * @method warning($message, array $context = []) static
 * @method notice($message, array $context = []) static
 * @method info($message, array $context = []) static
 * @method debug($message, array $context = []) static
 * @method log($level, $message, array $context = []) static
 * @method write($filePrefix, $message, array $context = []) static
 */
class Log extends Facade
{

    // 获取实例
    public static function getInstance()
    {
        return \Mix::app()->log;
    }

}
