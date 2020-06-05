<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/5/2020
 * Time: 9:14 AM
 */

declare(strict_types=1);

namespace App\Controllers\Torrents;

use App\Forms\Torrents\DownloadForm;
use Rid\Http\AbstractController;

class DownloadController extends AbstractController
{
    public function index()
    {
        $download = new DownloadForm();
        $download->setInput(container()->get('request')->query->all());
        if ($download->validate()) {
            $download->flush();
            return $download->sendFileContentToClient();
        } else {
            return $this->render('action/fail', ['msg' => $download->getError()]);
        }
    }
}
