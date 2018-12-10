<?php

namespace mix\facades;

use mix\base\Facade;

/**
 * Input 门面类
 * @author 刘健 <coder.liu@qq.com>
 *
 * @method getScriptFileName() static
 * @method getCommand() static
 * @method getCommandName() static
 * @method getCommandAction() static
 * @method getOptions() static
 */
class Input extends Facade
{

    // 获取实例
    public static function getInstance()
    {
        return \Mix::app()->input;
    }

}
