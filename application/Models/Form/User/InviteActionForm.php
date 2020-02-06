<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/6/7
 * Time: 17:59
 */

namespace App\Models\Form\User;

use App\Entity\User\UserStatus;

use Rid\Validators\Validator;

class InviteActionForm extends Validator
{
    public $uid;

    public $action;

    // $action = 'confirm'
    public $invitee_id;  // The user id of invitee
    private $confirm_info;

    // $action = 'recycle'
    public $invite_id;
    private $invite_info;

    const ACTION_CONFIRM = 'confirm';
    const ACTION_RECYCLE = 'recycle';

    public static function defaultData(): array
    {
        return [
            'uid' => app()->auth->getCurUser()->getId()
        ];
    }

    public static function inputRules(): array
    {
        return [
            'action' => [
                ['required'],
                ['InList', ['list' => [self::ACTION_CONFIRM, self::ACTION_RECYCLE]]]
            ],
            'username' => [
                ['required'],
                ['MaxLength', ['max' => 12], 'User name is too log, Max length {max}']
            ],
            'email' => 'required | email',
            'temp_id' => 'Integer',
        ];
    }

    public static function callbackRules(): array
    {
        return [
            'checkActionPrivilege',
            'checkConfirmInfo', 'checkRecycleInfo'
        ];
    }

    protected function checkActionPrivilege()
    {
        $action = $this->getInput('action');
        if ($action == self::ACTION_CONFIRM) {
            if (!app()->auth->getCurUser()->isPrivilege('invite_manual_confirm')) {
                $this->buildCallbackFailMsg('action:privilege', 'privilege is not enough to confirm pending user.');
            }
        } elseif ($action == self::ACTION_RECYCLE) {
            $check_recycle_privilege_name = ($this->getInput('uid') == app()->auth->getCurUser()->getId() ? 'invite_recycle_self_pending' : 'invite_recycle_other_pending');
            if (!app()->auth->getCurUser()->isPrivilege($check_recycle_privilege_name)) {
                $this->buildCallbackFailMsg('action:privilege', 'privilege is not enough to recycle user pending invites.');
            }
        }
    }

    protected function checkConfirmInfo()
    {
        if ($this->getInput('action') == self::ACTION_CONFIRM) {
            $this->confirm_info = app()->pdo->prepare('SELECT `status` FROM users WHERE id= :invitee_id')->bindParams([
                'invitee_id' => $this->getInput('invitee_id')
            ])->queryScalar();
            if ($this->confirm_info === false || $this->confirm_info !== UserStatus::PENDING) {
                $this->buildCallbackFailMsg('user:confirm', 'The user to confirm is not exist or already confirmed');
            }
        }
    }

    protected function checkRecycleInfo()
    {
        if ($this->getInput('action') == self::ACTION_RECYCLE) {
            // Get unused invite info
            $this->invite_info = app()->pdo->prepare('SELECT * FROM `invite` WHERE `id` = :invite_id AND `inviter_id` = :inviter_id AND `used` = 0')->bindParams([
                'invite_id' => $this->getInput('invite_id'), 'inviter_id' => $this->getInput('uid')
            ])->queryOne();

            if (!$this->invite_info) {
                $this->buildCallbackFailMsg('invite_info', 'this invite info is not exit');
                return;
            }

            // TODO Add recycle limit so that user can't make a temporarily invite like 'permanent'
            if ($this->invite_info['invite_type'] == InviteForm::INVITE_TYPE_TEMPORARILY) {
                if (app()->redis->get('invite_recycle_limit:user_' . $this->invite_info['inviter_id']) !== false) {
                    $this->buildCallbackFailMsg('invite_recycle_limit', 'Hit recycle limit');
                    return;
                };
            }
        }
    }

    private function flush_confirm()
    {
        app()->pdo->prepare('UPDATE `users` SET `status` = :new_status WHERE `id` = :invitee_id')->bindParams([
            'new_status' => UserStatus::CONFIRMED, 'invitee_id' => $this->invitee_id
        ])->execute();
        if (app()->pdo->getRowCount() > 1) {
            return 'Confirm Pending User Success!';
        } else {
            return 'You can\'t confirm a confirmed user.';
        }
    }

    private function flush_recycle()
    {
        app()->pdo->beginTransaction();
        try {
            // Set this invite record's status as recycled
            app()->pdo->prepare('UPDATE `invite` SET `used` = -2 WHERE `id` = :id')->bindParams([
                'id' => $this->invite_info['id'],
            ])->execute();
            $msg = 'Recycle invite success!';

            // Recycle or not ?
            if (config('invite.recycle_return_invite')) {
                if ($this->invite_info['invite_type'] == InviteForm::INVITE_TYPE_PERMANENT) {
                    app()->pdo->prepare('UPDATE `users` SET `invites` = `invites` + 1 WHERE id = :uid')->bindParams([
                        'uid' => $this->invite_info['inviter_id']
                    ])->execute();
                    $msg .= ' And return you a permanent invite';
                } elseif ($this->invite_info['invite_type'] == InviteForm::INVITE_TYPE_TEMPORARILY) {
                    app()->pdo->prepare('INSERT INTO `user_invitations` (`user_id`,`total`,`create_at`,`expire_at`) VALUES (:uid,:total,CURRENT_TIMESTAMP,DATE_ADD(NOW(),INTERVAL :life_time SECOND ))')->bindParams([
                        'uid' => $this->invite_info['inviter_id'], 'total' => 1,
                        'life_time' => config('invite.recycle_invite_lifetime')
                    ])->execute();
                    $msg .= ' And return you a temporarily invite with ' . config('invite.recycle_invite_lifetime') . ' seconds lifetime.';
                    app()->redis->hDel('User:' . $this->invite_info['inviter_id'] . ':base_content', 'temp_invite');
                }
            }
            app()->pdo->commit();
        } catch (\Exception $e) {
            $msg = '500 Error.....' . $e->getMessage();
            app()->pdo->rollback();
        }
        return $msg;
    }

    public function flush()
    {
        if ($this->action == self::ACTION_CONFIRM) {
            return $this->flush_confirm();
        } elseif ($this->action == self::ACTION_RECYCLE) {
            return $this->flush_recycle();
        }
    }
}
