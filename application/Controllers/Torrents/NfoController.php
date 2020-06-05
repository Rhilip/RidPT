<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/5/2020
 * Time: 10:36 AM
 */

declare(strict_types=1);

namespace App\Controllers\Torrents;

use App\Forms\Torrents\NfoForm;
use Rid\Http\AbstractController;

class NfoController extends AbstractController
{
    public function index()
    {
        $nfo = new NfoForm();
        $nfo->setInput(container()->get('request')->query->all());
        if ($nfo->validate()) {
            $nfo->flush();
            return $this->render('torrents/nfo', ['nfo'=>$nfo]);
        } else {
            return $this->render('action/fail', ['msg' => $nfo->getError()]);
        }
    }
}
