<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2018/12/31
 * Time: 11:26
 */

namespace apps\controllers;

use apps\models\form\User;

use Rid\Http\Controller;

class UserController extends Controller
{

    public function actionIndex()
    {
        return $this->actionDetails();
    }

    public function actionSetting()
    {
        return $this->render('user/setting');
    }

    public function actionInvite()
    {
        $msg = '';
        if (app()->request->isPost()) {
            $form = new User\InviteForm();
            $form->setInput(app()->request->post());
            $success = $form->validate();
            if ($success) {
                $form->flush();
                $msg = 'Send Invite Success!';
            } else {
                return $this->render('action/fail', ['title' => 'Invite Failed', 'msg' => $form->getError()]);
            }
        }

        $user = app()->auth->getCurUser();
        $uid = app()->request->get('uid');
        if (!is_null($uid) && $uid != app()->auth->getCurUser()->getId()) {
            if (app()->auth->getCurUser()->isPrivilege('view_invite')) {
                $user = app()->site->getUser($uid);
            } else {
                return $this->render('action/fail', ['title' => 'Fail', 'msg' => 'Privilege is not enough to see other people\'s invite status.']);
            }
        }

        // FIXME By using Form Class
        if (!is_null(app()->request->get('action'))) {
            $action_form = new User\InviteActionForm();
            $action_form->setInput(app()->request->get());
            $success = $action_form->validate();
            if ($success) {
                $msg = $action_form->flush();
            } else {
                return $this->render('action/fail', ['title' => 'Invite Failed', 'msg' => $action_form->getError()]);
            }
        }

        return $this->render('user/invite', ['user' => $user, 'msg' => $msg]);
    }


    public function actionDetails()
    {
        $panel = new User\UserDetailsForm();
        if (!$panel->validate()) {
            return $this->render('action/fail', ['msg' => $panel->getError()]);
        }

        return $this->render('user/details', ['details' => $panel]);
    }

    public function actionSessions()
    {
        if (app()->request->isPost()) {
            $action = app()->request->post('action');  // FIXME
            if ($action == 'revoke') {
                $to_del_session = app()->request->post('session');

                // expired it from Database first
                app()->pdo->createCommand('UPDATE `sessions` SET `expired` = 1 WHERE `uid` = :uid AND `session` = :sid')->bindParams([
                    'uid' => app()->auth->getCurUser()->getId(), 'sid' => $to_del_session
                ])->execute();
                $success = app()->pdo->getRowCount();

                if ($success > 0) {
                    app()->redis->zRem(app()->auth->getCurUser()->sessionSaveKey, $to_del_session);
                } else {
                    return $this->render('action/fail', ['title' => 'Remove Session Failed', 'msg' => 'Remove Session Failed']);
                }
            }
        }

        $session_list = new User\SessionsListForm();
        if (false === $session_list->validate()) {
            return $this->render('action/fail', ['msg' => $session_list->getError()]);
        }

        return $this->render('user/sessions', ['session_list' => $session_list]);
    }
}
