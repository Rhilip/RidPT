<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/2/8
 * Time: 18:51
 */

namespace apps\models\form;

use Rid\User\UserInterface;
use Rid\Validators\Validator;


class UserConfirmForm extends Validator
{

    protected $id;
    private $uid;
    public $secret;

    public static function inputRules()
    {
        return [
            'secret' => 'Required',
        ];
    }

    public static function callbackRules()
    {
        return ['validConfirmSecret'];
    }

    protected function validConfirmSecret()
    {
        $record = app()->pdo->createCommand(
            'SELECT `users_confirm`.`id`,`users_confirm`.`uid`,`users`.`status` FROM `users_confirm` 
                  LEFT JOIN `users` ON `users`.`id` = `users_confirm`.`uid`
                  WHERE `serect` = :uid LIMIT 1;')->bindParams([
            'uid' => $this->secret
        ])->queryOne();

        if ($record == false) {  // It means this confirm key is not exist
            $this->buildCallbackFailMsg('confirm key', 'This confirm key is not exist');
            return;
        }

        if ($record['status'] !== UserInterface::STATUS_PENDING) {
            $this->buildCallbackFailMsg('User', 'User Already Confirmed');
            return;
        }
        $this->uid = $record['uid'];
        $this->id = $record['id'];
    }

    public function flush()
    {
        app()->pdo->createCommand('UPDATE `users` SET `status` = :s WHERE `id` = :uid')->bindParams([
            's' => UserInterface::STATUS_CONFIRMED, 'uid' => $this->uid
        ])->execute();
        app()->pdo->createCommand('DELETE FROM `users_confirm` WHERE id = :id')->bindParams([
            'id' => $this->id
        ])->execute();
        app()->redis->del('User:content_' . $this->uid);
    }
}
