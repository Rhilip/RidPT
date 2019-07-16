<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/6/5
 * Time: 21:45
 */

namespace apps\models\form;


use Rid\Helpers\StringHelper;
use Rid\Http\View;

use apps\models\form\Auth\UserRegisterForm;

class UserInviteForm extends UserRegisterForm
{

    public $invite_type;
    public $temp_id;
    public $username;
    public $email;
    public $message;

    public $invite_link;

    public $type = 'invite';

    private $temp_record;

    const INVITE_TYPE_TEMPORARILY = 'temporarily';
    const INVITE_TYPE_PERMANENT = 'permanent';

    public static function inputRules()
    {
        return [
            'username' => [
                ['required'],
                ['MaxLength', ['max' => 12], 'User name is too log, Max length {max}']
            ],
            'email' => 'required | email',
            'invite_type' => [
                ['required'],
                ['InList', ['list' => [self::INVITE_TYPE_TEMPORARILY, self::INVITE_TYPE_PERMANENT]]]
            ],
            'temp_id' => 'Integer',
        ];
    }

    public static function callbackRules()
    {
        return [
            'isInviteSystemOpen', 'isRegisterSystemOpen', 'isMaxUserReached',
            'isValidUsername', 'isValidEmail',
            'canInvite', 'checkInviteInterval'
        ];
    }

    protected function isInviteSystemOpen()
    {
        if (config('base.enable_invite_system') != true) {
            $this->buildCallbackFailMsg('InviteSystemOpen', 'The invite system isn\'t open in this site.');
        }
    }

    /**
     * Check if user can invite
     */
    protected function canInvite()
    {
        // if user have enough invite number
        $invite_sum = app()->user->getInvites() + app()->user->getTempInvitesSum();
        if ($invite_sum <= 0) {
            $this->buildCallbackFailMsg('Invitation qualification', 'No enough invite qualification');
            return;
        }

        // If it is temporary invite
        if ($this->invite_type == self::INVITE_TYPE_TEMPORARILY) {
            $record = app()->pdo->createCommand('SELECT * FROM `user_invitations` WHERE id = :id AND user_id = :uid AND (`total`-`used`) > 0 AND `expire_at` > NOW()')->bindParams([
                'id' => $this->temp_id, 'uid' => app()->user->getId()
            ])->queryOne();
            if (false === $record) {
                $this->buildCallbackFailMsg('Temporary Invitation', 'Temporary Invitation is not exist, it may not belong to you or expired.');
                return;
            }
            $this->temp_record = $record;
        }
    }

    protected function checkInviteInterval()
    {
        if (!app()->user->isPrivilege('pass_invite_interval_check')) {
            $count = app()->pdo->createCommand([
                ['SELECT COUNT(`id`) FROM `invite` WHERE `create_at` > DATE_SUB(NOW(),INTERVAL :wait_second SECOND) ', 'params' => ['wait_second' => config('invite.interval')]],
                ['AND `used` = 0', 'if' => !config('invite.force_interval')]
            ])->queryScalar();
            if ($count > 0) {
                $this->buildCallbackFailMsg('Invitation interval', 'Hit invitation interval, please wait');
            }
        }
    }

    private function insertInviteRecord()
    {
        do { // To make sure this hash is unique !
            $invite_hash = StringHelper::getRandomString(32);

            $count = app()->pdo->createCommand('SELECT COUNT(`id`) FROM `invite` WHERE `hash` = :hash')->bindParams([
                'hash' => $invite_hash
            ])->queryScalar();
        } while ($count != 0);

        $this->invite_link = app()->request->root() . '/auth/register?' . http_build_query([
                'type' => 'invite',
                'invite_hash' => $invite_hash
            ]);

        app()->pdo->createCommand('INSERT INTO `invite` (`inviter_id`,`username`,`invite_type`, `hash`, `create_at`, `expire_at`) VALUES (:inviter_id,:username,:invite_type,:hash,NOW(),DATE_ADD(NOW(),INTERVAL :timeout SECOND))')->bindParams([
            'inviter_id' => app()->user->getId(), 'username' => $this->username, 'invite_type' => $this->invite_type,
            'hash' => $invite_hash, 'timeout' => config('invite.timeout')
        ])->execute();
    }

    public function flush()
    {
        // Consume the invite number
        app()->pdo->beginTransaction();
        try {
            if ($this->invite_type == self::INVITE_TYPE_TEMPORARILY) { // Consume the temp invite
                app()->pdo->createCommand('UPDATE `user_invitations` SET `used` = `used` + 1 WHERE `id` = :id')->bindParams([
                    'id' => $this->temp_id
                ])->execute();
            } else {  // Consume user privilege invite
                app()->pdo->createCommand('UPDATE `users` SET `invites` = `invites` - 1 WHERE `id` = :uid')->bindParams([
                    'uid' => app()->user->getId()
                ])->execute();
            }

            $this->insertInviteRecord();
            app()->redis->del('User:' . app()->user->getId() . ':base_content');  // flush it's cache

            $invite_status = true;
            app()->pdo->commit();
        } catch (\Exception $e) {
            $invite_status = $e->getMessage();
            app()->pdo->rollback();
        }

        if ($invite_status === true) {
            $mail_body = (new View(false))->render('email/user_invite', [
                'username' => $this->username,
                'invite_link' => $this->invite_link,
            ]);
            $mail_sender = \apps\libraries\Mailer::newInstanceByConfig('libraries.[mailer]');
            $mail_sender->send([$this->email], 'Invite To RidPT', $mail_body);
        }
        return $invite_status;
    }
}
