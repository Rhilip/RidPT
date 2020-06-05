<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/5/2020
 * Time: 9:00 AM
 */

declare(strict_types=1);

namespace App\Controllers\Torrents;

use App\Forms\Torrents\StructureForm;
use Rid\Http\AbstractController;

class StructureController extends AbstractController
{
    public function index()
    {
        $structure = new StructureForm();
        $structure->setInput(container()->get('request')->query->all());
        if ($structure->validate()) {
            return $this->render('torrents/structure', ['structure' => $structure]);
        } else {
            return $this->render('action/fail', ['msg' => $structure->getError()]);
        }
    }
}
