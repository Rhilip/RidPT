<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/4/2020
 * Time: 11:51 PM
 */

declare(strict_types=1);

namespace App\Controllers\Torrents;

use App\Forms\Torrents\DetailsForm;
use Rid\Http\AbstractController;

class DetailController extends AbstractController
{
    public function index()
    {
        $details = new DetailsForm();
        $details->setInput(container()->get('request')->query->all());
        if ($details->validate()) {
            return $this->render('torrents/detail', ['details' => $details]);
        } else {
            return $this->render('action/fail', ['msg' => $details->getError()]);
        }
    }
}
