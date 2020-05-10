<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 7/15/2019
 * Time: 10:20 PM
 */

namespace App\Controllers;

use Rid\Http\Controller;
use App\Models\Form\Manage\Categories;
use Symfony\Component\HttpFoundation\Request;

class ManageController extends Controller
{
    public function categories()
    {
        if (\Rid\Helpers\ContainerHelper::getContainer()->get('request')->isMethod(Request::METHOD_POST)) {
            if (\Rid\Helpers\ContainerHelper::getContainer()->get('request')->request->get('action') == 'cat_edit') {
                $edit_form = new Categories\EditForm();
                $edit_form->setInput(\Rid\Helpers\ContainerHelper::getContainer()->get('request')->request->all());
                $success = $edit_form->validate();
                if ($success) {
                    $edit_form->flush();
                    return $this->render('action/success');
                } else {
                    return $this->render('action/fail', ['msg' => $edit_form->getError()]);
                }
            } elseif (\Rid\Helpers\ContainerHelper::getContainer()->get('request')->request->get('action') == 'cat_delete') {
                $delete_form = new Categories\RemoveForm();
                $delete_form->setInput(\Rid\Helpers\ContainerHelper::getContainer()->get('request')->request->all());
                $success = $delete_form->validate();
                if ($success) {
                    $delete_form->flush();
                    return $this->render('action/success');
                } else {
                    return $this->render('action/fail', ['msg' => $delete_form->getError()]);
                }
            }
        }

        $categories = \Rid\Helpers\ContainerHelper::getContainer()->get('pdo')->prepare('SELECT * FROM `categories` ORDER BY `full_path`')->queryAll();

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
