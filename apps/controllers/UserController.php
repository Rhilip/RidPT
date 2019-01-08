<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2018/12/31
 * Time: 11:26
 */

namespace apps\controllers;

use Mix\Http\Controller;

class UserController extends Controller
{

    public function actionIndex()
    {
        return $this->actionPanel();
    }

    public function actionPanel()
    {

    }

    public function actionSetting()
    {

    }
}
