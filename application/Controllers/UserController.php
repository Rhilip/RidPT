<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2018/12/31
 * Time: 11:26
 */

namespace App\Controllers;

use App\Models\Form\User;

use Rid\Http\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class UserController extends AbstractController
{
    public function details()
    {
        $panel = new User\UserDetailsForm();
        $panel->setInput(container()->get('request')->query->all());
        if (!$panel->validate()) {
            return $this->render('action/fail', ['msg' => $panel->getError()]);
        }

        return $this->render('user/details', ['details' => $panel]);
    }

    public function setting()
    {
        return $this->render('user/setting');
    }

    public function invite()
    {
        $msg = '';
        if (container()->get('request')->isMethod(Request::METHOD_POST)) {
            $form = new User\InviteForm();
            $form->setInput(container()->get('request')->request->all());
            $success = $form->validate();
            if ($success) {
                $form->flush();
                $msg = 'Send Invite Success!';
            } else {
                return $this->render('action/fail', ['title' => 'Invite Failed', 'msg' => $form->getError()]);
            }
        }

        $user = container()->get('auth')->getCurUser();
        $uid = container()->get('request')->query->get('uid');
        if (!is_null($uid) && $uid != container()->get('auth')->getCurUser()->getId()) {
            if (container()->get('auth')->getCurUser()->isPrivilege('view_invite')) {
                $user = container()->get(\App\Entity\User\UserFactory::class)->getUserById($uid);
            } else {
                return $this->render('action/fail', ['title' => 'Fail', 'msg' => 'Privilege is not enough to see other people\'s invite status.']);
            }
        }

        // FIXME By using Form Class
        if (container()->get('request')->query->has('action')) {
            $action_form = new User\InviteActionForm();
            $action_form->setInput(container()->get('request')->query->all());
            $success = $action_form->validate();
            if ($success) {
                $msg = $action_form->flush();
            } else {
                return $this->render('action/fail', ['title' => 'Invite Failed', 'msg' => $action_form->getError()]);
            }
        }

        return $this->render('user/invite', ['user' => $user, 'msg' => $msg]);
    }


    public function sessions()
    {
        if (container()->get('request')->isMethod(Request::METHOD_POST)) {
            $action = container()->get('request')->request->get('action');  // FIXME
            if ($action == 'revoke') {
                $to_del_session = container()->get('request')->request->get('session');

                // expired it from Database first
                container()->get('pdo')->prepare('UPDATE `sessions` SET `expired` = 1 WHERE `uid` = :uid AND `session` = :sid')->bindParams([
                    'uid' => container()->get('auth')->getCurUser()->getId(), 'sid' => $to_del_session
                ])->execute();
                $success = container()->get('pdo')->getRowCount();

                if ($success > 0) {
                    container()->get('redis')->zRem(container()->get('auth')->getCurUser()->sessionSaveKey, $to_del_session);
                } else {
                    return $this->render('action/fail', ['title' => 'Remove Session Failed', 'msg' => 'Remove Session Failed']);
                }
            }
        }

        $session_list = new User\SessionsListForm();
        $session_list->setInput(container()->get('request')->query->all());
        if (false === $session_list->validate()) {
            return $this->render('action/fail', ['msg' => $session_list->getError()]);
        }

        return $this->render('user/sessions', ['session_list' => $session_list]);
    }
}
