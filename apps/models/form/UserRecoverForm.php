<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/6/4
 * Time: 14:42
 */

namespace apps\models\form;


use apps\components\User\UserInterface;
use Rid\Helpers\StringHelper;
use Rid\Http\View;
use Rid\Validators\CaptchaTrait;
use Rid\Validators\Validator;

class UserRecoverForm  extends Validator
{
    use CaptchaTrait;

    public $email;

    protected $_action = 'recover';

    public static function inputRules()
    {
        return [
            'email' => 'required | email',
        ];
    }

    public static function callbackRules()
    {
        return ['validateCaptcha'];
    }

    // TODO Add rate limit for user only can recover once in a time interval

    /**
     * Check email in our database and send recover link to that email
     * Notice: if this email is not exist in our database , will also return bool(true) for security reason.
     *         However, We will not send recover-confirm-link email.
     * @return bool|string  bool(true) means flush success ,
     *                      any other value (string) performs like error msg
     */
    public function flush() {
        // Check this email is in our database or not?
        $user_info = app()->pdo->createCommand('SELECT `id`,`username`,`status` FROM `users` WHERE `email` = :email;')->bindParams([
            'email' => $this->email
        ])->queryOne();
        if ($user_info !== false) {
            if ($user_info['status'] !== UserInterface::STATUS_CONFIRMED) {
                return 'std_user_account_unconfirmed';
            }

            // Send user email to get comfirm link
            $confirm_key = StringHelper::getRandomString(32);
            app()->pdo->createCommand('INSERT INTO `user_confirm` (`uid`,`serect`,`create_at`,`action`) VALUES (:uid,:serect,CURRENT_TIMESTAMP,:action)')->bindParams([
                'uid' => $user_info['id'], 'serect' => $confirm_key, 'action' => $this->_action
            ])->execute();
            $confirm_url = app()->request->root() . '/auth/confirm?' . http_build_query([
                    'secret' => $confirm_key,
                    'action' => $this->_action
                ]);

            $mail_body = (new View(false))->render('email/user_recover', [
                'username' => $user_info['username'],
                'confirm_url' => $confirm_url,
            ]);
            $mail_sender = \apps\Libraries\Mailer::newInstanceByConfig('libraries.[mailer]');
            $mail_sender->send([$this->email], 'Please confirm your action to recover your password', $mail_body);
        }
        return true;
    }


}
