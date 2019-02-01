<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2018/12/31
 * Time: 11:26
 */

namespace apps\controllers;

use apps\models\User;
use Rid\Http\Controller;

class UserController extends Controller
{

    public function actionIndex()
    {
        return $this->actionPanel();
    }

    public function actionSetting()
    {
        return $this->render('user/setting.html.twig');
    }

    public function actionPanel()
    {
        $uid = app()->request->get('id');
        if ($uid && $uid != app()->user->getId()) {
            $user = new User($uid);
        } else {
            $user = app()->user;
        }
        return $this->render('user/panel.html.twig', ['user' => $user]);
    }

    public function actionSessions()
    {
        if (app()->request->isPost()) {
            $action = app()->request->post('action');
            if ($action == 'delsession') {
                $to_del_session = app()->request->post('session');

                // expired it from Database first
                app()->pdo->createCommand('UPDATE `users_session_log` SET `expired` = 1 WHERE uid = :uid AND sid = :sid')->bindParams([
                    'uid' => app()->user->getId(), 'sid' => $to_del_session
                ])->execute();
                $success = app()->pdo->getRowCount();

                if ($success > 0) {
                    app()->redis->zRem(app()->user->sessionSaveKey, $to_del_session);
                } else {
                    return $this->render('errors/action_fail.html.twig', ['title' => 'Remove Session Failed', 'msg' => 'Remove Session Failed']);
                }
            }
        }

        $sessions = app()->pdo->createCommand('SELECT sid,login_at,INET6_NTOA(login_ip) as login_ip,user_agent,last_access_at FROM users_session_log WHERE uid=:uid and expired=0')->bindParams([
            'uid' => app()->user->getId()
        ])->queryAll();
        return $this->render('user/sessions.html.twig', ['sessions' => $sessions]);
    }
}
