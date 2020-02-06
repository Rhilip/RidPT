<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/6/15
 * Time: 17:12
 */

namespace App\Controllers;

use App\Models\Form\Links;
use Rid\Http\Controller;
use Symfony\Component\HttpFoundation\Request;

class LinksController extends Controller
{
    public function actionIndex()
    {
        return app()->response->setRedirect('/links/manage', 301);
    }

    public function actionApply()
    {
        if (app()->request->isMethod(Request::METHOD_POST)) {
            $form = new Links\ApplyForm();
            $form->setInput(app()->request->request->all());
            $success = $form->validate();
            if ($success) {
                $form->flush();
                return $this->render('action/success', ['msg' => __('form.link_apply.msg_success')]);
            } else {
                return $this->render('action/fail', ['msg' => $form->getError()]);
            }
        }

        return $this->render('links/apply');
    }

    public function actionManage()
    {
        if (app()->request->isMethod(Request::METHOD_POST)) {
            if (app()->request->request->get('action') == 'link_edit') {
                $edit_form = new Links\EditForm();
                $edit_form->setInput(app()->request->request->all());
                $success = $edit_form->validate();
                if ($success) {
                    $edit_form->flush();
                    return $this->render('action/success');
                } else {
                    return $this->render('action/fail', ['msg' => $edit_form->getError()]);
                }
            } elseif (app()->request->request->get('action') == 'link_delete') {
                $delete_form = new Links\RemoveForm();
                $delete_form->setInput(app()->request->request->all());
                $success = $delete_form->validate();
                if ($success) {
                    $delete_form->flush();
                    return $this->render('action/success');
                } else {
                    return $this->render('action/fail', ['msg' => $delete_form->getError()]);
                }
            }
        }


        $all_links = app()->pdo->prepare("SELECT * FROM `links` ORDER BY FIELD(`status`,'enabled','pending','disabled'),`id` ASC")->queryAll();

        return $this->render('links/manage', ['links' => $all_links]);
    }
}
