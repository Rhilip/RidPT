<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/6/2020
 * Time: 9:57 AM
 */

declare(strict_types=1);

namespace App\Controllers\Subtitles;

use App\Forms\Subtitles\DeleteForm;
use Rid\Http\AbstractController;

class DeleteController extends AbstractController
{
    /** @noinspection PhpUnused */
    public function takeDelete()
    {
        $delete = new DeleteForm();
        $delete->setInput(container()->get('request')->request->all());
        if ($delete->validate()) {
            $delete->flush();
            return $this->render('action/success', ['redirect' => '/subtitles']); // TODO add redirect
        } else {
            return $this->render('action/fail', ['msg' => $delete->getError()]);  // TODO add redirect
        }
    }
}
