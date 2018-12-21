<?php

namespace Mix\Facades;

use Mix\Base\Facade;

/**
 * Output 门面类
 * @author 刘健 <coder.liu@qq.com>
 *
 * @method ansiFormat($message, $color = \Mix\Console\Output::NONE) static
 * @method write($message, $color = \Mix\Console\Output::NONE) static
 * @method writeln($message, $color = \Mix\Console\Output::NONE) static
 */
class Output extends Facade
{

    // 获取实例
    public static function getInstance()
    {
        return \Mix::app()->output;
    }

}
