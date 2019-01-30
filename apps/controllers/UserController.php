<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2018/12/31
 * Time: 11:26
 */

namespace apps\controllers;

use apps\models\User;
use Mix\Http\Controller;

class UserController extends Controller
{

    public function actionIndex()
    {
        return $this->actionPanel();
    }

    public function actionPanel()
    {
        $uid = app()->request->get('id');
        if ($uid && $uid != app()->user->getId()) {
            $user = new User($uid);
        } else {
            $user = app()->user;
        }
        return $this->render('user/panel.html.twig',['user' => $user]);
    }

    public function actionSetting()
    {

    }
}
