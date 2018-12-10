<?php

namespace mix\facades;

use mix\base\Facade;

/**
 * Session 门面类
 * @author 刘健 <coder.liu@qq.com>
 *
 * @method createSessionId() static
 * @method set($name, $value) static
 * @method get($name = null) static
 * @method has($name) static
 * @method delete($name) static
 * @method clear() static
 * @method getSessionId() static
 */
class Session extends Facade
{

    // 获取实例
    public static function getInstance()
    {
        return \Mix::app()->session;
    }

}
