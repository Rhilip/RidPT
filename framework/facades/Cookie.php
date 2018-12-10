<?php

namespace mix\facades;

use mix\base\Facade;

/**
 * Cookie 门面类
 * @author 刘健 <coder.liu@qq.com>
 *
 * @method set($name, $value, $expire = null) static
 * @method get($name = null) static
 * @method has($name) static
 * @method delete($name) static
 * @method clear() static
 */
class Cookie extends Facade
{

    // 获取实例
    public static function getInstance()
    {
        return \Mix::app()->cookie;
    }

}
