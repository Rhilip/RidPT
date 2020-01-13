<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 8/17/2019
 * Time: 2019
 */

namespace App\Controllers;

use App\Models\Form\Site;
use Rid\Http\Controller;

class SiteController extends Controller
{
    public function actionRules()
    {
        return $this->render('site/rules');
    }

    public function actionLogs()
    {
        $logs = new Site\Logs();
        if (!$logs->validate()) {
            return $this->render('action/fail', ['msg'=>$logs->getError()]);
        }
        return $this->render('site/logs', ['logs'=>$logs]);
    }
}
