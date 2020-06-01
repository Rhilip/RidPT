<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/1/2020
 * Time: 2020
 */

declare(strict_types=1);

namespace App\Controllers\Auth;


use App\Forms\Auth\LogoutForm;
use Rid\Http\AbstractController;

class LogoutController extends AbstractController
{
    public function index() {
        $logout = new LogoutForm();
        $logout->setInput(container()->get('request')->query->all());
        if (false === $logout->validate()) {
            return $this->render('action/fail', ['msg' => $logout->getError()]);
        } else {
            $logout->flush();
        }

        return container()->get('response')->setRedirect('/auth/login');
    }
}
