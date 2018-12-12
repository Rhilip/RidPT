<?php

namespace apps\httpd\middleware;

use mix\facades\PDO;
use mix\facades\Request;
use mix\facades\Session;
use mix\facades\Response;

/**
 * 前置中间件
 * @author 刘健 <coder.liu@qq.com>
 */
class BeforeMiddleware
{

    public function handle($callable, \Closure $next)
    {
        list($controller, $action) = $callable;
        $controllerName = get_class($controller);

        $userInfo = Session::get('userInfo');

        if ($controllerName === "apps\httpd\controllers\AuthController") {
            if ($action !== "actionLogout")
                return $next();  // FIXME 更细粒度的控制
        }

        if (empty($userInfo)) {
            return Response::redirect("/auth/login");
        }

        // Update user status
        PDO::createCommand("UPDATE `users` SET last_access_at = NOW(), last_access_ip = INET6_ATON(:ip) WHERE id = :id")->bindParams([
            "ip" => Request::getClientIp(), "id" => $userInfo["uid"]
        ])->execute();

        // 执行下一个中间件
        return $next();
    }

}
