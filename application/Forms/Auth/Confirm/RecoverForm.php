<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/1/2020
 * Time: 2020
 */

declare(strict_types=1);

namespace App\Forms\Auth\Confirm;

use App\Enums\User\Status as UserStatus;
use Rid\Utils\Random;

class RecoverForm extends AbstractConfirmForm
{
    protected string $action = 'recover';

    public function flush()
    {
        if ($this->record['user_status'] !== UserStatus::CONFIRMED) {
            $this->msg =  'user status is not confirmed , they may in pending or banned';  // FIXME msg
            return;
        }

        // generate new password
        $new_password = Random::alnum(10);
        container()->get('pdo')->prepare('UPDATE `users` SET `password` = :new_password WHERE `id` = :uid')->bindParams([
            'new_password' => password_hash($new_password, PASSWORD_DEFAULT), 'uid' => $this->record['uid']
        ])->execute();
        $this->update_confirm_status();

        // Send user email to tell his new password.
        container()->get('site')->sendEmail(
            $this->record['email'],
            'New Password',
            'email/user_new_password',
            [
                'username' => $this->record['username'],
                'password' => $new_password,
            ]
        );
        $this->msg = 'Your password has been reset and new password has been send to your email, Please find it and login.';
    }
}
