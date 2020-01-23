<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 8/11/2019
 * Time: 2019
 */

namespace App\Controllers;

use Rid\Http\Controller;

class MaintenanceController extends Controller
{
    public function actionIndex()
    {
        // Check if site is on maintenance status
        if (!config('base.maintenance')) {
            return app()->response->setRedirect('/index');
        }

        return $this->render('maintenance');
    }
}
