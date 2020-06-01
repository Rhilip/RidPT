<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/1/2020
 * Time: 2020
 */

declare(strict_types=1);

namespace App\Controllers\Auth;


use App\Forms\Auth\RecoverForm;
use Rid\Http\AbstractController;

class RecoverController extends AbstractController
{
    public function index() {
        return $this->render('auth/recover');
    }

    /** @noinspection PhpUnused */
    public function takeRecover() {
        $form = new RecoverForm();
        $form->setInput(container()->get('request')->request->all());
        $success = $form->validate();
        if (!$success) {
            return $this->render('action/fail', [
                'title' => 'Action Failed',
                'msg' => $form->getError()
            ]);
        } else {
            $flush = $form->flush();
            if ($form->getMsg()) {
                return $this->render('action/fail', [
                    'title' => 'Confirm Failed',
                    'msg' => $flush
                ]);
            } else {
                return $this->render('auth/recover_next_step');
            }
        }
    }
}
