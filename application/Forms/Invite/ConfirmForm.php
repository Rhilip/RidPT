<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/7/2020
 * Time: 12:21 PM
 */

declare(strict_types=1);

namespace App\Forms\Invite;

use App\Enums\User\Status as UserStatus;
use Rid\Validators\AbstractValidator;
use Rid\Validators\Constraints as AcmeAssert;
use Symfony\Component\Validator\Constraints as Assert;

class ConfirmForm extends AbstractValidator
{
    protected function loadInputMetadata(): Assert\Collection
    {
        return new Assert\Collection([
            'user_id' => new AcmeAssert\PositiveInt()
        ]);
    }

    protected function loadCallbackMetaData(): array
    {
        return ['canConfirmInvite', 'checkConfirmInfo'];
    }

    public function flush(): void
    {
        container()->get('dbal')->prepare('UPDATE `users` SET `status` = :new_status WHERE `id` = :invitee_id')->bindParams([
            'new_status' => UserStatus::CONFIRMED, 'invitee_id' => $this->getInput('user_id')
        ])->execute();
    }

    /** @noinspection PhpUnused */
    protected function canConfirmInvite()
    {
        if (!container()->get('auth')->getCurUser()->isPrivilege('invite_manual_confirm')) {
            $this->buildCallbackFailMsg('action:privilege', 'privilege is not enough to confirm pending user.');
        }
    }

    /** @noinspection PhpUnused */
    protected function checkConfirmInfo()
    {
        $confirm_info = container()->get('dbal')->prepare('SELECT `status` FROM users WHERE id = :id')->bindParams([
            'id' => $this->getInput('user_id')
        ])->fetchScalar();
        if ($confirm_info === false || $confirm_info !== UserStatus::PENDING) {
            $this->buildCallbackFailMsg('user:confirm', 'The user to confirm is not exist or already confirmed');
        }
    }
}
