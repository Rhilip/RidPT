<?php

namespace mix\facades;

use mix\base\Facade;

/**
 * Request 门面类
 * @author 刘健 <coder.liu@qq.com>
 *
 * @method get($name = null) static
 * @method post($name = null) static
 * @method files($name = null) static
 * @method route($name = null) static
 * @method cookie($name = null) static
 * @method server($name = null) static
 * @method header($name = null) static
 * @method isGet() static
 * @method isPost() static
 * @method isPut() static
 * @method isPatch() static
 * @method isDelete() static
 * @method isHead() static
 * @method isOptions() static
 * @method method() static
 * @method root() static
 * @method path() static
 * @method url() static
 * @method fullUrl() static
 * @method getRawBody() static
 * @method getClientIp() static
 * @method getClientIps() static
 * @method isSecure() static
 */
class Request extends Facade
{

    // 获取实例
    public static function getInstance()
    {
        return \Mix::app()->request;
    }

}
