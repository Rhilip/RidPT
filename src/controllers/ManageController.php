<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 7/15/2019
 * Time: 10:20 PM
 */

namespace apps\controllers;

use Rid\Http\Controller;
use apps\models\form\Manage\Categories;

class ManageController extends Controller
{
    public function actionCategories()
    {
        if (app()->request->isPost()) {
            if (app()->request->post('action') == 'cat_edit') {
                $edit_form = new Categories\EditForm();
                $edit_form->setInput(app()->request->post());
                $success = $edit_form->validate();
                if ($success) {
                    $edit_form->flush();
                    return $this->render('action/success');
                } else {
                    return $this->render('action/fail', ['msg' => $edit_form->getError()]);
                }
            } elseif (app()->request->post('action') == 'cat_delete') {
                $delete_form = new Categories\RemoveForm();
                $delete_form->setInput(app()->request->post());
                $success = $delete_form->validate();
                if ($success) {
                    $delete_form->flush();
                    return $this->render('action/success');
                } else {
                    return $this->render('action/fail', ['msg' => $delete_form->getError()]);
                }
            }
        }

        $categories = app()->pdo->createCommand('SELECT * FROM `categories` ORDER BY `full_path`')->queryAll();

        return $this->render('manage/categories', ['categories' => $categories]);
    }

    public function actionQualities()
    {
        // TODO
    }

    public function actionTags()
    {
        // TODO
    }

    public function actionTeams()
    {
        // TODO
    }
}
