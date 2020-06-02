<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/1/2020
 * Time: 2020
 */

declare(strict_types=1);

namespace App\Controllers\Auth;

use App\Forms\Auth\LoginForm;
use Rid\Http\AbstractController;

class LoginController extends AbstractController
{
    /** @noinspection PhpUnused */
    public function index($render_data = [])
    {
        $render_data['test_attempts'] = container()->get('redis')->hGet('Site:fail_login_ip_count', container()->get('request')->getClientIp()) ?: 0;
        return $this->render('auth/login', $render_data);
    }

    /** @noinspection PhpUnused */
    public function takeLogin()
    {
        $login = new LoginForm();
        $login->setInput(container()->get('request')->request->all());
        if (false === $login->validate()) {
            $this->loginFailed();
            $render_data[] = $login->getError();
            return $this->index(['error_msg' => $login->getError()]);
        } else {
            $login->flush();

            $return_to = container()->get('session')->pop('login_return_to') ?? '/index';
            return container()->get('response')->setRedirect($return_to);
        }
    }

    private function loginFailed()
    {
        $user_ip = container()->get('request')->getClientIp();
        $test_attempts = container()->get('redis')->hIncrBy('Site:fail_login_ip_count', $user_ip, 1);
        if ($test_attempts >= config('security.max_login_attempts')) {
            container()->get('site')->banIp($user_ip);
        }
    }
}
