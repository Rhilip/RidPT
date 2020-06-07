<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/7/2020
 * Time: 9:00 PM
 */

declare(strict_types=1);

namespace App\Controllers\Invite;

use App\Forms\Invite\RecycleForm;
use Rid\Http\AbstractController;

class RecycleController extends AbstractController
{
    /** @noinspection PhpUnused */
    public function takeRecycle()
    {
        $form = new RecycleForm();
        $form->setInput(container()->get('request')->request->all());
        if ($form->validate()) {
            $form->flush();
            return $this->render('action/success', ['redirect' => '/invite']);
        } else {
            return $this->render('action/fail', ['msg' => $form->getError()]);
        }
    }
}
