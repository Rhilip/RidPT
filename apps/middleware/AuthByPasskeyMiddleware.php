<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/3/10
 * Time: 17:25
 */

namespace apps\middleware;


class AuthByPasskeyMiddleware
{
    public function handle($callable, \Closure $next)
    {

        // Check User
        if (app()->request->get('passkey') === null) {
            return 'missing passkey';
        }

        $user = app()->site->getCurUser('passkey');
        if (!$user) {
            return 'passkey not exist';
        }

        // 执行下一个中间件
        return $next();
    }
}
