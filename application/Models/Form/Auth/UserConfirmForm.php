<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/2/8
 * Time: 18:51
 */

namespace App\Models\Form\Auth;

use App\Entity\User\UserStatus;

use Rid\Helpers\StringHelper;
use Rid\Validators\Validator;

class UserConfirmForm extends Validator
{
    public $action;

    const ACTION_REGISTER = 'register';
    const ACTION_RECOVER = 'recover';

    protected $id;
    private $uid;
    private $email;
    private $username;
    private $user_status;

    public static function inputRules(): array
    {
        return [
            'secret' => 'Required',
            'action' => [
                ['Required'],
                ['InList', ['list' => [self::ACTION_REGISTER, self::ACTION_RECOVER]], 'Unknown confirm action.']
            ],
        ];
    }

    public static function callbackRules(): array
    {
        return ['validConfirmSecret'];
    }

    /**
     * Verity The confirm secret and action exist in table `user_confirm` or not
     *
     * @noinspection PhpUnused
     */
    protected function validConfirmSecret()
    {
        $record = app()->pdo->prepare(
            'SELECT `user_confirm`.`id`,`user_confirm`.`uid`,`users`.`status`,`users`.`username`,`users`.`email` FROM `user_confirm`
                  LEFT JOIN `users` ON `users`.`id` = `user_confirm`.`uid`
                  WHERE `secret` = :secret AND `action` = :action AND used = 0 LIMIT 1;'
        )->bindParams([
            'secret' => $this->getInput('secret'), 'action' => $this->getInput('action')
        ])->queryOne();

        if ($record == false) {  // It means this confirm key is not exist
            $this->buildCallbackFailMsg('confirm key', 'This confirm key is not exist');  // FIXME msg
            return;
        }

        $this->id = $record['id'];
        $this->uid = $record['uid'];
        $this->email = $record['email'];
        $this->username = $record['username'];
        $this->user_status = $record['status'];
    }

    private function update_confirm_status()
    {
        app()->pdo->prepare('UPDATE `user_confirm` SET `used` = 1 WHERE id = :id')->bindParams([
            'id' => $this->id
        ])->execute();
    }

    private function flush_register()
    {
        if ($this->user_status !== UserStatus::PENDING) {
            return 'user status is not pending , they may already confirmed or banned';  // FIXME msg
        }

        app()->pdo->prepare('UPDATE `users` SET `status` = :s WHERE `id` = :uid')->bindParams([
            's' => UserStatus::CONFIRMED, 'uid' => $this->uid
        ])->execute();
        $this->update_confirm_status();
        app()->redis->del('User:content_' . $this->uid);
        return true;
    }

    private function flush_recover()
    {
        if ($this->user_status !== UserStatus::CONFIRMED) {
            return 'user status is not confirmed , they may in pending or banned';  // FIXME msg
        }

        // generate new password
        $new_password = StringHelper::getRandomString(10);
        app()->pdo->prepare('UPDATE `users` SET `password` = :new_password WHERE `id` = :uid')->bindParams([
            'new_password' => password_hash($new_password, PASSWORD_DEFAULT), 'uid' => $this->uid
        ])->execute();
        $this->update_confirm_status();

        // Send user email to tell his new password.
        app()->site->sendEmail(
            [$this->email],
            'New Password',
            'email/user_new_password',
            [
                'username' => $this->username,
                'password' => $new_password,
            ]
        );

        return true;
    }

    public function flush()
    {
        if ($this->action == self::ACTION_REGISTER) {
            return $this->flush_register();
        } elseif ($this->action == self::ACTION_RECOVER) {
            return $this->flush_recover();
        }
    }

    public function getConfirmMsg()
    {
        if ($this->action == self::ACTION_REGISTER) {
            return 'Your account is success Confirmed.'; // FIXME i18n
        } elseif ($this->action == self::ACTION_RECOVER) {
            return 'Your password has been reset and new password has been send to your email, Please find it and login.';
        }
    }
}
