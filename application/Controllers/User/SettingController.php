<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/7/2020
 * Time: 10:29 PM
 */

declare(strict_types=1);

namespace App\Controllers\User;

use Rid\Http\AbstractController;

class SettingController extends AbstractController
{
    // FIXME
    public function index()
    {
        return $this->render('user/setting');
    }
}
