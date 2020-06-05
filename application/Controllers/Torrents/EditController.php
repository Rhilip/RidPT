<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/5/2020
 * Time: 11:15 AM
 */

declare(strict_types=1);

namespace App\Controllers\Torrents;

use App\Forms\Torrents\DetailsForm;
use App\Forms\Torrents\EditForm;
use Rid\Http\AbstractController;

class EditController extends AbstractController
{
    public function index()
    {
        $exist = new DetailsForm();
        $exist->setInput(container()->get('request')->query->all());
        if ($exist->validate()) {
            $exist->flush();

            if (container()->get('auth')->getCurUser()->getId() != $exist->getTorrent()->getOwnerId()  // User is torrent owner
                || !container()->get('auth')->getCurUser()->isPrivilege('manage_torrents')  // User can manager torrents
            ) {
                return $this->render('action/fail', ['msg' => 'You can\'t edit torrent which is not belong to you.']);
            } else {
                return $this->render('torrents/edit', ['edit' => $exist]);
            }
        } else {
            return $this->render('action/fail', ['msg' => $exist->getError()]);
        }
    }

    public function takeEdit()
    {
        $edit = new EditForm();
        $edit->setInput(container()->get('request')->request->all() + container()->get('request')->files->all());
        if ($edit->validate()) {
            $edit->flush();
            return container()->get('response')->setRedirect('/torrents/detail?id=' . $edit->getTorrent()->getId());
        } else {
            return $this->render('action/fail', ['msg' => $edit->getError()]);
        }
    }
}
