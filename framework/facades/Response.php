<?php

namespace mix\facades;

use mix\base\Facade;

/**
 * Response 门面类
 * @author 刘健 <coder.liu@qq.com>
 *
 * @method setHeader($key, $value) static
 * @method setCookie($name, $value = "", $expire = 0, $path = "", $domain = "", $secure = false, $httponly = false) static
 * @method redirect($url) static
 */
class Response extends Facade
{

    // 获取实例
    public static function getInstance()
    {
        return \Mix::app()->response;
    }

}
