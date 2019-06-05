<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/2/8
 * Time: 18:51
 */

namespace apps\models\form;

use apps\components\User\UserInterface;
use Rid\Helpers\StringHelper;
use Rid\Validators\Validator;


class UserConfirmForm extends Validator
{

    public $secret;
    public $action;

    protected static $action_list = ['register', 'recover'];

    protected $id;
    private $uid;
    private $user_status;

    public static function inputRules()
    {
        return [
            'secret' => 'Required',
            'action' => [
                ['Required'],
                ['InList', ['list' => self::$action_list], 'Unknown confirm action.']
            ],
        ];
    }

    public static function callbackRules()
    {
        return ['validConfirmSecret'];
    }

    /**
     * Verity The confirm secret and action exist in table `user_confirm` or not
     */
    protected function validConfirmSecret()
    {
        $record = app()->pdo->createCommand(
            'SELECT `user_confirm`.`id`,`user_confirm`.`uid`,`users`.`status` FROM `user_confirm` 
                  LEFT JOIN `users` ON `users`.`id` = `user_confirm`.`uid`
                  WHERE `serect` = :serect AND `action` = :action AND used = 0 LIMIT 1;')->bindParams([
            'serect' => $this->secret , 'action' => $this->action
        ])->queryOne();

        if ($record == false) {  // It means this confirm key is not exist
            $this->buildCallbackFailMsg('confirm key', 'This confirm key is not exist');  // FIXME msg
            return;
        }

        $this->uid = $record['uid'];
        $this->id = $record['id'];
        $this->user_status = $record['status'];
    }

    protected function update_confirm_status() {
        app()->pdo->createCommand('UPDATE `user_confirm` SET `used` = 1 WHERE id = :id')->bindParams([
            'id' => $this->id
        ])->execute();
    }

    public function flush_register() {
        if ($this->user_status !==  UserInterface::STATUS_PENDING) {
            return 'user status is not pending , they may already confirmed or banned';  // FIXME msg
        }

        app()->pdo->createCommand('UPDATE `users` SET `status` = :s WHERE `id` = :uid')->bindParams([
            's' => UserInterface::STATUS_CONFIRMED, 'uid' => $this->uid
        ])->execute();
        $this->update_confirm_status();
        app()->redis->del('User:content_' . $this->uid);
        return true;
    }

    public function flush_recover() {
        if ($this->user_status !==  UserInterface::STATUS_CONFIRMED) {
            return 'user status is not confirmed , they may still in pending or banned';  // FIXME msg
        }

        // generate new password
        $new_password = StringHelper::getRandomString(10);
        app()->pdo->createCommand('UPDATE `users` SET `password` = :new_password WHERE `id` = :uid')->bindParams([
            'new_password'=> password_hash($new_password, PASSWORD_DEFAULT), 'uid'=>$this->uid
        ])->execute();
        $this->update_confirm_status();

        // TODO Send user email to tell his new password.




        return true;
    }

    public function flush() {
        return $this->{'flush_' . $this->action}();  // Magic function call
    }
}
