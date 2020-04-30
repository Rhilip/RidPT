<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/1/8
 * Time: 21:50
 */

namespace App\Controllers;

use Rid\Helpers\ContainerHelper;

class CaptchaController
{
    public function actionIndex()
    {
        app()->response->headers->set('Content-Type', 'image/png');
        $captcha = ContainerHelper::getContainer()->get('captcha');
        $captcha->generate();
        app()->session->set('captchaText', $captcha->getText());
        return $captcha->getContent();
    }
}
