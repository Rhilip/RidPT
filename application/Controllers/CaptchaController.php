<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/1/8
 * Time: 21:50
 */

namespace App\Controllers;

use Rid\Http\AbstractController;
use Rid\Libraries\Captcha;

class CaptchaController extends AbstractController
{
    public function index()
    {
        container()->get('response')->headers->set('Content-Type', 'image/png');
        $captcha = container()->get(Captcha::class);
        $captcha->generate();
        container()->get('session')->set('captchaText', $captcha->getText());
        return $captcha->getContent();
    }
}
