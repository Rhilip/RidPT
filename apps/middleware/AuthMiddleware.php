<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 8/11/2019
 * Time: 2019
 */

namespace apps\middleware;

use apps\controllers;
use apps\libraries\Constant;

class AuthMiddleware
{
    const authByPasskeyControllers = [
        controllers\RssController::class
    ];

    /** @noinspection PhpUnused */
    public function handle($callable, \Closure $next)
    {
        list($controller, $action) = $callable;
        $controllerName = get_class($controller);

        $authby = in_array($controllerName, self::authByPasskeyControllers) ? 'passkey' : 'cookies';
        $curuser = app()->site->getCurUser($authby, true);

        if (config('base.prevent_anonymous') && $curuser === false) return app()->response->setStatusCode(403);
        if (config('base.maintenance') && !$curuser->isPrivilege('bypass_maintenance')) return app()->response->redirect('/maintenance');
        return $this->{'authBy' . ucfirst($authby)}($callable, $next);
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */
    private function authByPasskey($callable, \Closure $next) {
        if (false === $curuser = app()->site->getCurUser('passkey')) {
            return 'invalid Passkey';
        }
        return $next();
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */
    private function authByCookies($callable, \Closure $next) {
        list($controller, $action) = $callable;
        $controllerName = get_class($controller);

        $curuser = app()->site->getCurUser();

        $now_ip = app()->request->getClientIp();
        if ($controllerName === controllers\AuthController::class) {
            if ($curuser !== false && in_array($action, ['actionLogin', 'actionRegister', 'actionConfirm'])) {
                /** Don't allow Logged in user visit the auth/{login, register, confirm} */
                return app()->response->redirect('/index');
            } elseif ($action !== 'actionLogout') {
                if ($action == 'actionLogin') {  // TODO add register confirm fail ip count check
                    $test_count = app()->redis->hGet('SITE:fail_login_ip_count', $now_ip) ?: 0;
                    if ($test_count > config('security.max_login_attempts')) {
                        return app()->response->setStatusCode(403);
                    }
                }
                return $next();
            }
        }

        if (false === $curuser) {
            app()->cookie->delete(Constant::cookie_name);  // Delete exist cookies
            app()->session->set('login_return_to', app()->request->fullUrl());  // Store the url which visitor want to hit
            return app()->response->redirect('/auth/login');
        } else {
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
