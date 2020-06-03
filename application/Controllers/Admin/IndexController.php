<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/3/2020
 * Time: 6:14 PM
 */

declare(strict_types=1);

namespace App\Controllers\Admin;


use Rid\Http\AbstractController;

class IndexController extends AbstractController
{
    public function index() {
        return $this->render('admin/index');
    }
}
