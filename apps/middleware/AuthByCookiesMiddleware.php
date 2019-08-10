<?php

namespace apps\middleware;


use apps\libraries\Constant;

class AuthByCookiesMiddleware
{

    public function handle($callable, \Closure $next)
    {
        list($controller, $action) = $callable;
        $controllerName = get_class($controller);

        $curuser = app()->site->getCurUser();

        $now_ip = app()->request->getClientIp();
        if ($controllerName === \apps\controllers\AuthController::class) {
            if ($curuser !== false && in_array($action, ['actionLogin', 'actionRegister', 'actionConfirm'])) {
                return app()->response->redirect('/index');
            } elseif ($action !== 'actionLogout') {
                if ($action == 'actionLogin') {
                    $test_count = app()->redis->hGet('SITE:fail_login_ip_count', app()->request->getClientIp()) ?: 0;
                    if ($test_count > config('security.max_login_attempts')) {
                        return app()->response->setStatusCode(403);
                    }
                }
                return $next();
            }
        }

        if (false === $curuser) {
            $query = app()->request->server('query_string');
            $to = app()->request->server('path_info') . (strlen($query) > 0 ? '?' . $query : '');
            app()->session->set('login_return_to', $to);
            return app()->response->redirect('/auth/login');
        } else {
            /**
             * Check if session is locked with IP
             */
            $userSessionId = app()->request->cookie(Constant::cookie_name);
            if (substr($userSessionId, 0, 1) === '1') {
                $record_ip_crc = substr($userSessionId, 2, 8);
                $this_ip_crc = sprintf('%08x', crc32($now_ip));

                if (strcasecmp($record_ip_crc, $this_ip_crc) !== 0) {  // The Ip isn't matched
                    app()->cookie->delete(Constant::cookie_name);
                    return app()->response->redirect('/auth/login');
                }
            }

            /** Check User Permission to this route
             *
             * When user visit - /admin -> Controller : \apps\controllers\AdminController  Action: actionIndex
             * it will check the dynamic config key `authority.route_admin_index` and compare with curuser class ,
             * if user don't have this permission to visit this route the http code 403 will throw out.
             * if this config key is not exist , the default class 1 will be used to compare.
             *
             * Example of `Route - Controller - Config Key` Map:
             * /admin          -> AdminController::actionIndex     ->  route.admin_index
             * /admin/service  -> AdminController::actionService   ->  route.admin_service
             */
            $route = strtolower(str_replace(
                    ['apps\\controllers\\', 'Controller', 'action'], '',
                    $controllerName . '_' . $action
                )
            );
            $required_class = config('route.' . $route) ?: 1;
            if ($curuser->getClass() < $required_class) {
                return app()->response->setStatusCode(403);  // FIXME redirect to /error may better
            }

            // We will not update user last_access_ip if it not change or expired
            $last_access_ip = app()->redis->get('user:' . $curuser->getId() . ':access_ip');
            if ($last_access_ip === false || $last_access_ip !== $now_ip) {
                app()->pdo->createCommand('UPDATE `users` SET last_access_at = NOW(), last_access_ip = INET6_ATON(:ip) WHERE id = :id;')->bindParams([
                    'ip' => $now_ip, 'id' => $curuser->getId()
                ])->execute();
                app()->redis->set('user:' . $curuser->getId() . ':access_ip', $now_ip, 3600);
            }
        }

        // 执行下一个中间件
        return $next();
    }

}
