<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2018/12/31
 * Time: 11:26
 */

namespace apps\controllers;

use apps\models\User;
use apps\models\form\UserInviteForm;

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
                return $this->render('errors/action_fail', ['title' => 'Invite Failed', 'msg' => $form->getError()]);
            }
        }

        // FIXME
        if (app()->request->get('action') == 'confirm' && app()->user->isPrivilege('invite_manual_confirm')) {
            $uid = app()->request->get('uid');
            if ($uid) {
                app()->pdo->createCommand("UPDATE `users` SET `status` = 'confirmed' WHERE `id` = :invitee_id AND `status` = 'pending'")->bindParams([
                    'invitee_id' => $uid
                ])->execute();
                if (app()->pdo->getRowCount() > 1) {
                    $msg = 'Confirm Pending User Success!';
                } else {
                    $msg = 'You can\'t confirm a confirmed user.';
                }
            }
        }

        if (app()->request->get('action') == 'recycle' && app()->user->isPrivilege('invite_recycle_pending')) {
            $invite_id = app()->request->get('invite_id');
            if ($invite_id) {
                // Get unused invite info
                $invite_info = app()->pdo->createCommand('SELECT * FROM `invite` WHERE `id` = :invite_id AND `inviter_id` = :inviter_id AND `used` = 0')->bindParams([
                    'invite_id' => $invite_id, 'inviter_id' => app()->user->getId()
                ])->queryOne();
                if ($invite_info) {
                    app()->pdo->beginTransaction();
                    try {
                        // Set this record used
                        app()->pdo->createCommand('UPDATE `invite` SET `used` = 1 WHERE `id` = :id')->bindParams([
                            'id' => $invite_info['id'],
                        ])->execute();
                        $msg = 'Recycle invite success!';

                        // Recycle or not ?
                        if (app()->config->get('invite.recycle_return_invite')) {
                            // TODO Add recycle limit so that user can't make a temporarily invite like 'permanent'
                            if ($invite_info['invite_type'] == 'permanent') {
                                app()->pdo->createCommand('UPDATE `users` SET `invites` = `invites` + 1 WHERE id = :uid')->bindParams([
                                    'uid' => $invite_id['inviter_id']
                                ])->execute();
                                $msg .= ' And return you a permanent invite';
                            } elseif ($invite_info['invite_type'] == 'temporarily') {
                                app()->pdo->createCommand('INSERT INTO `user_invitations` (`user_id`,`total`,`expire_at`) VALUES (:uid,:total,DATE_ADD(NOW(),INTERVAL :life_time SECOND ))')->bindParams([
                                    'uid' => app()->user->getId(), 'total' => 1,
                                    'life_time' => app()->config->get('invite.recycle_invite_lifetime')
                                ])->execute();
                                $msg .= ' And return you a temporarily invite with ' . app()->config->get('invite.recycle_invite_lifetime') . ' seconds lifetime.';
                                app()->redis->hDel( 'User:' . app()->user->getId() . ':base_content','temp_invite');
                            }
                        }
                        app()->pdo->commit();
                    } catch (\Exception $e) {
                        $msg = '500 Error.....' . $e->getMessage();
                        app()->pdo->rollback();
                    }
                } else {
                    $msg = 'Can\'t Found this invite record.';
                }
            }
        }

        $user = app()->user;

        $uid = app()->request->get('id');
        if (!is_null($uid) && $uid != app()->user->getId()) {
            if (app()->user->isPrivilege('view_invite')) {
                $user = new User($uid);
            } else {
                return $this->render('errors/action_fail', ['title' => 'Fail', 'msg' => 'Privilege is not enougth to see other people\'s invite status.']);
            }
        }

        return $this->render('user/invite', ['user' => $user, 'msg' => $msg]);
    }


    public function actionPanel()
    {
        $uid = app()->request->get('id');
        if ($uid && $uid != app()->user->getId()) {
            $user = new User($uid);
        } else {
            $user = app()->user;
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
                    'uid' => app()->user->getId(), 'sid' => $to_del_session
                ])->execute();
                $success = app()->pdo->getRowCount();

                if ($success > 0) {
                    app()->redis->zRem(app()->user->sessionSaveKey, $to_del_session);
                } else {
                    return $this->render('errors/action_fail', ['title' => 'Remove Session Failed', 'msg' => 'Remove Session Failed']);
                }
            }
        }

        $sessions = app()->pdo->createCommand('SELECT sid,login_at,login_ip,user_agent,last_access_at FROM user_session_log WHERE uid = :uid and expired = 0')->bindParams([
            'uid' => app()->user->getId()
        ])->queryAll();
        return $this->render('user/sessions', ['sessions' => $sessions]);
    }
}
