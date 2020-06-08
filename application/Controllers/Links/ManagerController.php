<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/2/2020
 * Time: 8:04 PM
 */

declare(strict_types=1);

namespace App\Controllers\Links;

use App\Forms\Links;
use Rid\Http\AbstractController;

class ManagerController extends AbstractController
{
    public function index()
    {
        $all_links = container()->get('dbal')->prepare("SELECT * FROM `links`")->fetchAll();
        return $this->render('links/manage', ['links' => $all_links]);
    }

    /** @noinspection PhpUnused */
    public function takeEdit()
    {
        $edit_form = new Links\EditForm();
        $edit_form->setInput(container()->get('request')->request->all());
        if ($edit_form->validate()) {
            $edit_form->flush();
            return $this->render('action/success', ['redirect' => '/links/manage']);
        } else {
            return $this->render('action/fail', ['msg' => $edit_form->getError()]);
        }
    }

    /** @noinspection PhpUnused */
    public function takeDelete()
    {
        $remove_form = new Links\DeleteForm();
        $remove_form->setInput(container()->get('request')->request->all());
        if ($remove_form->validate()) {
            $remove_form->flush();
            return $this->render('action/success', ['redirect' => '/links/manage']);
        } else {
            return $this->render('action/fail', ['msg' => $remove_form->getError()]);
        }
    }
}
