<?php

namespace mix\facades;

use mix\base\Facade;

/**
 * Error 门面类
 * @author 刘健 <coder.liu@qq.com>
 *
 * @method handleException($e) static
 */
class Error extends Facade
{

    // 获取实例
    public static function getInstance()
    {
        return \Mix::app()->error;
    }

}
