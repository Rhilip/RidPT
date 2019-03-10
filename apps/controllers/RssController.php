<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/3/10
 * Time: 9:14
 */

namespace apps\controllers;


class RssController
{
    public function actionIndex() {
        app()->user->loadUserFromPasskey();



        return 'TODO';
    }
}
