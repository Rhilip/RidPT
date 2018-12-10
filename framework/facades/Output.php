<?php

namespace mix\facades;

use mix\base\Facade;

/**
 * Output 门面类
 * @author 刘健 <coder.liu@qq.com>
 *
 * @method ansiFormat($message, $color = \mix\console\Output::NONE) static
 * @method write($message, $color = \mix\console\Output::NONE) static
 * @method writeln($message, $color = \mix\console\Output::NONE) static
 */
class Output extends Facade
{

    // 获取实例
    public static function getInstance()
    {
        return \Mix::app()->output;
    }

}
