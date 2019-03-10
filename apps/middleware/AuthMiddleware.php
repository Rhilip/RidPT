<?php

namespace apps\middleware;


class AuthMiddleware
{

    public function handle($callable, \Closure $next)
    {
        list($controller, $action) = $callable;
        $controllerName = get_class($controller);

        $isAnonymousUser = app()->user->isAnonymous();

        if ($controllerName === \apps\controllers\AuthController::class) {
            if (!$isAnonymousUser && in_array($action, ['actionLogin', 'actionRegister','actionConfirm'])) {
                return app()->response->redirect("/index");
            } elseif ($action !== "actionLogout") {
                if ($action == 'actionLogin') {
                    $test_count = app()->redis->hGet('SITE:fail_login_ip_count', app()->request->getClientIp()) ?: 0;
                    if ($test_count > app()->config->get('security.max_login_attempts')) {
                        return app()->response->setStatusCode(403);
                    }
                }
                return $next();
            }
        }

        if ($isAnonymousUser) {
            $to = app()->request->server('path_info') . '?' . app()->request->server('query_string');
            app()->session->set('login_return_to', $to);
            return app()->response->redirect("/auth/login");
        } else {
            /** Check User Permission to this route
             *
             * When user visit - /admin -> Controller : \apps\controllers\AdminController  Action: actionIndex
             * it will check the dynamic config key `authority.route_admin_index` and compare with curuser class ,
             * if user don't have this permission to visit this route the http code 403 will throw out.
             * if this config key is not exist , the default class 1 will be used to compare.
             *
             * Example of `Route - Controller - Config Key` Map:
             * /admin          -> AdminController::actionIndex     ->  authority.route_admin_index
             * /admin/service  -> AdminController::actionService   ->  authority.route_admin_service
             */
            $route = strtolower(str_replace(['apps\\controllers\\', 'Controller'], ['', ''], $controllerName)) .
                "_" . strtolower(str_replace('action', '', $action));
            $required_class = app()->config->get('authority.route_' . $route, false) ?: 1;
            if (app()->user->getClass(true) < $required_class) {
                return app()->response->setStatusCode(403);
            }
        }

        // Update user status
        app()->pdo->createCommand("UPDATE `users` SET last_access_at = NOW(), last_access_ip = INET6_ATON(:ip) WHERE id = :id;")->bindParams([
            "ip" => app()->request->getClientIp(), "id" => app()->user->getId()
        ])->execute();

        // 执行下一个中间件
        return $next();
    }

}
