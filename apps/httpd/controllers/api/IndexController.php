<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2018/12/11
 * Time: 21:01
 */

namespace apps\httpd\controllers\api;


class IndexController
{
    public function actionIndex()
    {
        return ["Hello" => "world"];
    }
}