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
    const authByPasskeyAction = [
        [controllers\RssController::class, 'actionIndex'],
        [controllers\TorrentController::class, 'actionDownload']
    ];

    /** @noinspection PhpUnused */
    public function handle($callable, \Closure $next)
    {
        list($controller, $action) = $callable;
        $controllerName = get_class($controller);

        // Try auth by cookies first
        $curuser = app()->auth->getCurUser('cookies', true);

        // if fails and in special route `/rss?passkey=` or `/torrent/download?passkey=` , then try auth by passkey
        if ($curuser === false) {
            foreach (self::authByPasskeyAction as $value) {
                list($_controller, $_action) = $value;
                if ($controllerName == $_controller && $action == $_action) {
                    $curuser = app()->auth->getCurUser('passkey', true);
                    break;
                }
            }
        }

        if (config('base.prevent_anonymous') && $curuser === false) return app()->response->setStatusCode(403);
        if (config('base.maintenance') && !$curuser->isPrivilege('bypass_maintenance')) return app()->response->redirect('/maintenance');
        return $this->{'authBy' . ucfirst(app()->auth->getGrant())}($callable, $next);
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */
    private function authByPasskey($callable, \Closure $next) {
        if (false === $curuser = app()->auth->getCurUser('passkey')) {
            return 'invalid Passkey';
        }
        return $next();
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */
    private function authByCookies($callable, \Closure $next) {
        list($controller, $action) = $callable;
        $controllerName = get_class($controller);

        $curuser = app()->auth->getCurUser();

        $now_ip = app()->request->getClientIp();
        if ($controllerName === controllers\AuthController::class) {
            if ($curuser !== false && in_array($action, ['actionLogin', 'actionRegister', 'actionConfirm'])) {
                /** Don't allow Logged in user visit the auth/{login, register, confirm} */
                return app()->response->redirect('/index');
            } elseif ($action !== 'actionLogout') {
                if ($action == 'actionLogin') {  // TODO add register confirm fail ip count check
                    $test_count = app()->redis->hGet('Site:fail_login_ip_count', $now_ip) ?: 0;
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
            /**
             * TODO move to Auth Component
             * Check User Permission to this route
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
        }

        // 执行下一个中间件
        return $next();
    }
}
