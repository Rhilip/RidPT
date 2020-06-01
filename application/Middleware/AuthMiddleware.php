<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 8/11/2019
 * Time: 2019
 */

namespace App\Middleware;

use App\Components\Auth;
use App\Controllers;
use App\Libraries\Constant;
use DI\Container;
use Rid\Http\Middleware\AbstractMiddleware;

class AuthMiddleware extends AbstractMiddleware
{
    const authByPasskeyAction = [
        [Controllers\RssController::class, 'actionIndex'],  // `/rss?passkey=`
        [Controllers\TorrentController::class, 'actionDownload']  // `/torrent/download?passkey=`
    ];

    /** @noinspection PhpUnused */
    public function handle($callable, \Closure $next)
    {
        list($controllerName, $action) = $callable;

        // Try auth by cookies first
        $curuser = container()->get('auth')->getCurUser('cookies', true);

        // Try auth by passkey in special route and action if first cookies-check fails
        if ($curuser === false) {
            foreach (self::authByPasskeyAction as $value) {
                list($_controller, $_action) = $value;
                if ($controllerName == $_controller && $action == $_action) {
                    $curuser = container()->get('auth')->getCurUser('passkey', true);
                    break;
                }
            }
        }

        // Check if Site in Maintenance status, and only let `bypass_maintenance` user access
        if (config('base.maintenance') && ($curuser === false || !$curuser->isPrivilege('bypass_maintenance'))) {
            return container()->get('response')->setRedirect('/maintenance');
        }

        // Deal with Anonymous Visitor
        if ($curuser === false) {
            // Check if Site in Abnormal status
            if (config('base.prevent_anonymous')) {
                return container()->get('response')->setStatusCode(403);
            }

            if (container()->get('auth')->getGrant() == 'passkey') {
                return 'invalid Passkey';
            } else {  // container()->get('auth')->getGrant() == 'cookies'
                // If visitor want to auth himself
                if ($controllerName === Controllers\AuthController::class && $action !== 'actionLogout') {
                    return $next();
                }

                // Prevent Other Route
                container()->get('response')->headers->clearCookie(Constant::cookie_name);  // Delete exist cookies
                container()->get('session')->set('login_return_to', container()->get('request')->getUri());  // Store the url which visitor want to hit
                return container()->get('response')->setRedirect('/auth/login');
            }
        }

        // Don't allow Logged in user visit the auth/{login, register, confirm}
        if ($controllerName === Controllers\AuthController::class &&
            in_array($action, ['actionLogin', 'actionRegister', 'actionConfirm'])) {
            return container()->get('response')->setRedirect('/index');
        }

        /**
         * Check User Permission to this route
         *
         * When user visit - /admin -> Controller : \src\controllers\AdminController  Action: actionIndex
         * it will check the dynamic config key `authority.route_admin_index` and compare with curuser class ,
         * if user don't have this permission to visit this route the http code 403 will throw out.
         * if this config key is not exist , the default class 1 will be used to compare.
         *
         * Example of `Route - Controller - Config Key` Map:
         * /admin          -> AdminController::actionIndex     ->  route.admin_index
         * /admin/service  -> AdminController::actionService   ->  route.admin_service
         */
        $route = strtolower(
            str_replace(
                ['App\\Controllers\\', 'Controller', 'action'],
                '',
                $controllerName . '_' . $action
            )
        );

        $required_class = config('route.' . $route) ?: 1;
        if ($curuser->getClass() < $required_class) {
            return container()->get('response')->setStatusCode(403);  // FIXME redirect to /error may better
        }

        return $next(); // 执行下一个中间件
    }
}
