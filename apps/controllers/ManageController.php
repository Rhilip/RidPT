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
                $edit_form->setData(app()->request->post());
                $success = $edit_form->validate();
                if ($success) {
                    $edit_form->flush();
                    return $this->render('action/action_success');
                } else {
                    return $this->render('action/action_fail', ['msg' => $edit_form->getError()]);
                }
            } elseif (app()->request->post('action') == 'cat_delete') {
                $delete_form = new Categories\RemoveForm();
                $delete_form->setData(app()->request->post());
                $success = $delete_form->validate();
                if ($success) {
                    $delete_form->flush();
                    return $this->render('action/action_success');
                } else {
                    return $this->render('action/action_fail', ['msg' => $delete_form->getError()]);
                }
            }
        }

        $parent_id = app()->request->get('parent_id', 0);
        $parent_category = [];
        if ($parent_id !== 0) {
            $parent_category = app()->pdo->createCommand('SELECT * FROM `torrents_categories` WHERE `id` = :id')->bindParams([
                'id' => $parent_id
            ])->queryOne();
        }

        $categories = app()->pdo->createCommand('SELECT * FROM `torrents_categories` WHERE `parent_id` = :pid ORDER BY `sort_index`,`id`')->bindParams([
            'pid' => $parent_id
        ])->queryAll();

        foreach ($categories as &$category) {
            $child_count = app()->pdo->createCommand('SELECT COUNT(`id`) FROM `torrents_categories` WHERE `parent_id` = :pid')->bindParams([
                'pid' => $category['id']
            ])->queryScalar();
            $category['child_count'] = $child_count;
        }

        return $this->render('manage/categories', ['parent_id' => $parent_id, 'parent_category' => $parent_category, 'categories' => $categories]);
    }
}
