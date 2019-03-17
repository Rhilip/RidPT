<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/3/17
 * Time: 12:41
 */

namespace apps\controllers;


class TestController
{
    public function actionIndex() {
        app()->getServ()->task(json_encode(
            [
                'worker' => \apps\task\EchoTask::class,
                'data' => 'Hello World',
            ]
        ));
    }
}
