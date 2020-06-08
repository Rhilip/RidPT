<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/1/2020
 * Time: 2020
 */

declare(strict_types=1);

namespace App\Forms\Auth;

use App\Enums\User\Status as UserStatus;
use App\Forms\Traits\CaptchaTrait;
use Rid\Utils\Random;
use Rid\Validators\AbstractValidator;
use Symfony\Component\Validator\Constraints as Assert;

class RecoverForm extends AbstractValidator
{
    use CaptchaTrait;

    private ?string $msg;

    protected function loadInputMetadata(): Assert\Collection
    {
        return new Assert\Collection([
            'email' => new Assert\Email()
        ]);
    }

    protected function loadCallbackMetaData(): array
    {
        return ['validateCaptcha'];
    }

    public function flush(): void
    {
        // Check this email is in our database or not?
        $user_info = container()->get('dbal')->prepare('SELECT `id`, `username`, `status` FROM `users` WHERE `email` = :email;')->bindParams([
            'email' => $this->getInput('email')
        ])->fetchOne();
        if ($user_info !== false) {
            if ($user_info['status'] !== UserStatus::CONFIRMED) {
                $this->msg = 'std_user_account_unconfirmed';
                return;
            }

            // Send user email to get comfirm link
            $confirm_key = Random::alnum(32);
            container()->get('dbal')->prepare('INSERT INTO `user_confirm` (`uid`, `secret`, `create_at`, `action`) VALUES (:uid,:secret,CURRENT_TIMESTAMP,:action)')->bindParams([
                'uid' => $user_info['id'], 'secret' => $confirm_key, 'action' => 'recover'
            ])->execute();
            $confirm_url = container()->get('request')->getSchemeAndHttpHost() . '/auth/confirm/recover?secret=' . $confirm_key;

            container()->get('site')->sendEmail(
                $this->getInput('email'),
                'Please confirm your action to recover your password',
                'email/user_recover',
                [
                    'username' => $user_info['username'],
                    'confirm_url' => $confirm_url,
                ]
            );
        }
    }

    /**
     * @return string|null
     */
    public function getMsg(): ?string
    {
        return $this->msg;
    }
}
