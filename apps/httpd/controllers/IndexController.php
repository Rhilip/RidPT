<?php

namespace apps\httpd\controllers;

use Mix\Http\Controller;

class IndexController extends Controller
{

    // 默认动作
    public function actionIndex()
    {
        return $this->render("index.html.twig");
    }
}
