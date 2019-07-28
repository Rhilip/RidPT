<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2018/12/31
 * Time: 11:26
 */

namespace apps\controllers;

use apps\models\form\UserInviteForm;
use apps\models\form\UserInviteActionForm;

use Rid\Http\Controller;

class UserController extends Controller
{

    public function actionIndex()
    {
        return $this->actionPanel();
    }

    public function actionSetting()
    {
        return $this->render('user/setting');
    }

    public function actionInvite()
    {
        $msg = '';
        if (app()->request->isPost()) {
            $form = new UserInviteForm();
            $form->setData(app()->request->post());
            $success = $form->validate();
            if ($success) {
                $form->flush();
                $msg = 'Send Invite Success!';
            } else {
                return $this->render('action/action_fail', ['title' => 'Invite Failed', 'msg' => $form->getError()]);
            }
        }

        $user = app()->site->getCurUser();
        $uid = app()->request->get('uid');
        if (!is_null($uid) && $uid != app()->site->getCurUser()->getId()) {
            if (app()->site->getCurUser()->isPrivilege('view_invite')) {
                $user = app()->site->getUser($uid);
            } else {
                return $this->render('action/action_fail', ['title' => 'Fail', 'msg' => 'Privilege is not enough to see other people\'s invite status.']);
            }
        }

        // FIXME By using Form Class
        if (!is_null(app()->request->get('action'))) {
            $action_form = new UserInviteActionForm();
            $action_form->setData(app()->request->get());
            $success = $action_form->validate();
            if ($success) {
                $msg = $action_form->flush();
            } else {
                return $this->render('action/action_fail', ['title' => 'Invite Failed', 'msg' => $action_form->getError()]);
            }
        }

        return $this->render('user/invite', ['user' => $user, 'msg' => $msg]);
    }


    public function actionPanel()
    {
        $uid = app()->request->get('id');
        if ($uid && $uid != app()->site->getCurUser()->getId()) {
            $user = app()->site->getUser($uid);
        } else {
            $user = app()->site->getCurUser();
        }
        return $this->render('user/panel', ['user' => $user]);
    }

    public function actionSessions()
    {
        if (app()->request->isPost()) {
            $action = app()->request->post('action');
            if ($action == 'delsession') {
                $to_del_session = app()->request->post('session');

                // expired it from Database first
                app()->pdo->createCommand('UPDATE `user_session_log` SET `expired` = 1 WHERE uid = :uid AND sid = :sid')->bindParams([
                    'uid' => app()->site->getCurUser()->getId(), 'sid' => $to_del_session
                ])->execute();
                $success = app()->pdo->getRowCount();

                if ($success > 0) {
                    app()->redis->zRem(app()->site->getCurUser()->sessionSaveKey, $to_del_session);
                } else {
                    return $this->render('action/action_fail', ['title' => 'Remove Session Failed', 'msg' => 'Remove Session Failed']);
                }
            }
        }

        $sessions = app()->pdo->createCommand('SELECT sid,login_at,login_ip,user_agent,last_access_at FROM user_session_log WHERE uid = :uid and expired = 0')->bindParams([
            'uid' => app()->site->getCurUser()->getId()
        ])->queryAll();
        return $this->render('user/sessions', ['sessions' => $sessions]);
    }
}
