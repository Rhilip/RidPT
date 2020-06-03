<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/3/2020
 * Time: 6:16 PM
 */

declare(strict_types=1);

namespace App\Controllers\Admin\Service;

use App\Forms\Admin\Service\RedisForm;
use Rid\Http\AbstractController;

class RedisController extends AbstractController
{
    public function index()
    {
        return $this->render('admin/service/redis', ['redis' => new RedisForm()]);
    }
}
