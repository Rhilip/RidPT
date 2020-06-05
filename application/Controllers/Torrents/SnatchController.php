<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/5/2020
 * Time: 9:30 AM
 */

declare(strict_types=1);

namespace App\Controllers\Torrents;

use App\Forms\Torrents\SnatchForm;
use Rid\Http\AbstractController;

class SnatchController extends AbstractController
{
    public function index()
    {
        $snatch = new SnatchForm();
        $snatch->setInput(container()->get('request')->query->all());
        if ($snatch->validate()) {
            $snatch->flush();
            return $this->render('torrents/snatch', ['snatch' => $snatch]);
        } else {
            return $this->render('action/fail', ['msg' => $snatch->getError()]);
        }
    }
}
