<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/3/2020
 * Time: 7:01 PM
 */

declare(strict_types=1);

namespace App\Controllers\Admin\Service;

use App\Forms\Admin\Service\MysqlForm;
use Rid\Http\AbstractController;

class MysqlController extends AbstractController
{
    public function index()
    {
        return $this->render('admin/service/mysql', ['mysql' => new MysqlForm()]);
    }
}
