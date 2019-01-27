<?php

namespace apps\middleware;


class BeforeMiddleware
{

    public function handle($callable, \Closure $next)
    {
        list($controller, $action) = $callable;
        $controllerName = get_class($controller);

        $isAnonymousUser = app()->user->isAnonymous();

        if ($controllerName === \apps\controllers\AuthController::class) {
            if (!$isAnonymousUser && in_array($action, ["actionLogin", "actionRegister"])) {
                return app()->response->redirect("/index");
            } elseif ($action !== "actionLogout") {
                return $next();
            }
        }

        if ($isAnonymousUser) {
            return app()->response->redirect("/auth/login");
        }

        // Update user status
        app()->pdo->createCommand("UPDATE `users` SET last_access_at = NOW(), last_access_ip = INET6_ATON(:ip) WHERE id = :id;")->bindParams([
            "ip" => app()->request->getClientIp(), "id" => app()->user->getId()
        ])->execute();

        // 执行下一个中间件
        return $next();
    }

}
