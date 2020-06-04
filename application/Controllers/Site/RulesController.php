<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/3/2020
 * Time: 10:56 PM
 */

declare(strict_types=1);

namespace App\Controllers\Site;

use Rid\Http\AbstractController;

class RulesController extends AbstractController
{
    public function index()
    {
        return $this->render('site/rules');
    }
}
