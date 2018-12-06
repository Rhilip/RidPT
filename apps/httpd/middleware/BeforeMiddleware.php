<?php

namespace apps\httpd\middleware;

use mix\facades\PDO;
use mix\facades\Request;
use mix\facades\Token;

/**
 * 前置中间件
 * @author 刘健 <coder.liu@qq.com>
 */
class BeforeMiddleware
{

    public function handle($callable, \Closure $next)
    {
        // 添加中间件执行代码
        $userInfo = Token::get('userInfo');
        list($controller, $action) = $callable;
        $controllerName = get_class($controller);

        if ($controllerName === "apps\httpd\controllers\TrackerController") {
            return $next();
        } elseif ($controllerName === "apps\httpd\controllers\AuthController") {
            if ($action !== "actionLogout")
                return $next();  // FIXME 更细粒度的控制
        }

        // For other visit , deny no access_token for other controller fetch
        if (empty($userInfo)) {
            return ['errcode' => 300000, 'errmsg' => 'Permission denied'];
        }

        // Update user status
        PDO::createCommand("UPDATE `users` SET last_access_at = NOW(), last_access_ip = INET6_ATON(:ip) WHERE id = :id")->bindParams([
            "ip" => Request::getClientIp(), "id" => $userInfo["uid"]
        ])->execute();

        // 执行下一个中间件
        return $next();
    }

}
