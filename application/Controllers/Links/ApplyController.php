<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/1/2020
 * Time: 2020
 */

declare(strict_types=1);

namespace App\Controllers\Links;

use App\Forms\Links\ApplyForm;
use Rid\Http\AbstractController;

class ApplyController extends AbstractController
{
    public function index()
    {
        return $this->render('links/apply');
    }

    public function takeApply()
    {
        $form = new ApplyForm();
        $form->setInput(container()->get('request')->request->all());
        $success = $form->validate();
        if ($success) {
            $form->flush();
            return $this->render('action/success', ['msg' => __('form.link_apply.msg_success')]);
        } else {
            return $this->render('action/fail', ['msg' => $form->getError()]);
        }
    }
}
