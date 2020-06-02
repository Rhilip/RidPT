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

class RegisterForm extends AbstractConfirmForm
{
    protected string $action = 'register';

    public function flush()
    {
        if ($this->record['user_status'] !== UserStatus::PENDING) {
            $this->msg = 'user status is not pending, they may already confirmed or banned';
            return;
        }

        container()->get('pdo')->prepare('UPDATE `users` SET `status` = :s WHERE `id` = :uid')->bindParams([
            's' => UserStatus::CONFIRMED, 'uid' => $this->record['uid']
        ])->execute();
        $this->update_confirm_status();
        container()->get('redis')->del('User:content_' .  $this->record['uid']);
        $this->msg = 'Your account is success Confirmed.';
    }
}
