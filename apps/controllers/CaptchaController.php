<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/1/8
 * Time: 21:50
 */

namespace apps\controllers;

use Mix\Http\Captcha;

class CaptchaController
{
    public function actionIndex()
    {
        app()->response->setHeader('Content-Type', 'image/png');
        $captcha = new Captcha([
            'width'      => 100,
            'height'     => 40,
            'fontFile'   => app()->basePath . '/fonts/Times New Roman.ttf',
            'fontSize'   => 20,
            'wordNumber' => 4,
            'angleRand'  => [-20, 20],
            'xSpacing'   => 0.82,
            'yRand'      => [5, 15],
        ]);
        $captcha->generate();
        app()->session->set('captchaText', $captcha->getText());
        return $captcha->getContent();
    }
}
