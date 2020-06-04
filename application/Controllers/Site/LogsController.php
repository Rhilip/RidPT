<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/3/2020
 * Time: 10:57 PM
 */

declare(strict_types=1);

namespace App\Controllers\Site;

use App\Forms\Site\LogsForm;
use Rid\Http\AbstractController;

class LogsController extends AbstractController
{
    public function index()
    {
        $logs = new LogsForm();
        $logs->setInput(container()->get('request')->query->all());
        if ($logs->validate()) {
            $logs->flush();
            return $this->render('site/logs', ['logs'=>$logs]);
        } else {
            return $this->render('action/fail', ['msg'=>$logs->getError()]);
        }
    }
}
