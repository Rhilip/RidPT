<?php

namespace apps\controllers;

use Rid\Http\Controller;

class IndexController extends Controller
{

    // 默认动作
    public function actionIndex()
    {
        return $this->render('index');
    }
}
