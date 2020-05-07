<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/1/8
 * Time: 21:50
 */

namespace App\Controllers;

use Rid\Http\Controller;

class CaptchaController extends Controller
{
    public function actionIndex()
    {
        app()->response->headers->set('Content-Type', 'image/png');
        $captcha = $this->container->get('captcha');
        $captcha->generate();
        $this->container->get('session')->set('captchaText', $captcha->getText());
        return $captcha->getContent();
    }
}
