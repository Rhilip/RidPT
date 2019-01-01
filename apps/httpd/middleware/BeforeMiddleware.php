<?php

namespace apps\httpd\middleware;

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

        $userInfo = app()->session->get('user');

        if ($controllerName === "apps\httpd\controllers\AuthController") {
            if ($userInfo && in_array($action, ["actionLogin", "actionRegister"])) {
                return app()->response->redirect("/index");
            } elseif ($action !== "actionLogout") {
                return $next();
            }
        }

        if (empty($userInfo)) {
            return app()->response->redirect("/auth/login");
        }

        // Update user status
        app()->pdo->createCommand("UPDATE `users` SET last_access_at = NOW(), last_access_ip = INET6_ATON(:ip) WHERE id = :id")->bindParams([
            "ip" => app()->request->getClientIp(), "id" => $userInfo["id"]
        ])->execute();

        // 执行下一个中间件
        return $next();
    }

}
