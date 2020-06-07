<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/7/2020
 * Time: 12:21 PM
 */

declare(strict_types=1);

namespace App\Forms\Invite;

use App\Enums\Invite\Type;
use Rid\Validators\AbstractValidator;
use Rid\Validators\Constraints as AcmeAssert;
use Symfony\Component\Validator\Constraints as Assert;

class RecycleForm extends AbstractValidator
{
    //private ?array $invite_info;


    protected function loadInputMetadata(): Assert\Collection
    {
        return new Assert\Collection([
            'id' => new AcmeAssert\PositiveInt()
        ]);
    }

    protected function loadCallbackMetaData(): array
    {
        return ['canRecycleInvite', 'checkRecycleInfo'];
    }

    public function flush(): void
    {
        container()->get('pdo')->beginTransaction();
        try {
            // Set this invite record's status as recycled
            container()->get('pdo')->prepare('UPDATE `invite` SET `used` = -2 WHERE `id` = :id')->bindParams([
                'id' => $this->getInput('id'),
            ])->execute();
            //$msg = 'Recycle invite success!';

            // Recycle or not ?
            /** FIXME
             * if (config('invite.recycle_return_invite')) {
             *
             * if ($this->invite_info['invite_type'] == InviteForm::INVITE_TYPE_PERMANENT) {
             * container()->get('pdo')->prepare('UPDATE `users` SET `invites` = `invites` + 1 WHERE id = :uid')->bindParams([
             * 'uid' => $this->invite_info['inviter_id']
             * ])->execute();
             * $msg .= ' And return you a permanent invite';
             * } elseif ($this->invite_info['invite_type'] == InviteForm::INVITE_TYPE_TEMPORARILY) {
             * container()->get('pdo')->prepare('INSERT INTO `user_invitations` (`user_id`,`total`,`create_at`,`expire_at`) VALUES (:uid,:total,CURRENT_TIMESTAMP,DATE_ADD(NOW(),INTERVAL :life_time SECOND ))')->bindParams([
             * 'uid' => $this->invite_info['inviter_id'], 'total' => 1,
             * 'life_time' => config('invite.recycle_invite_lifetime')
             * ])->execute();
             * $msg .= ' And return you a temporarily invite with ' . config('invite.recycle_invite_lifetime') . ' seconds lifetime.';
             * container()->get('redis')->hDel('User:' . $this->invite_info['inviter_id'] . ':base_content', 'temp_invite');
             * }
             * }
             */
            container()->get('pdo')->commit();
        } catch (\Exception $e) {
            //$msg = '500 Error.....' . $e->getMessage();
            container()->get('pdo')->rollback();
        }
        //return $msg;
    }

    /** @noinspection PhpUnused */
    protected function canRecycleInvite()
    {
        if (!container()->get('auth')->getCurUser()->isPrivilege('invite_recycle_pending')) {
            $this->buildCallbackFailMsg('action:privilege', 'privilege is not enough to recycle user pending invites.');
        }
    }

    /** @noinspection PhpUnused */
    protected function checkRecycleInfo()
    {
        // Get unused invite info
        $invite_info = container()->get('pdo')->prepare('SELECT * FROM `invite` WHERE `id` = :id AND `inviter_id` = :inviter_id AND `used` = 0')->bindParams([
            'id' => $this->getInput('id'),
            'inviter_id' => container()->get('auth')->getCurUser()->getId()
        ])->queryOne();

        if (!$invite_info) {
            $this->buildCallbackFailMsg('invite_info', 'this invite info is not exit');
            return;
        }

        if ($invite_info['invite_type'] == Type::TEMPORARILY) {
            $this->buildCallbackFailMsg('invite_info', 'Temporarily Invite Not Allow recycle');
            return;
        }
        //$this->invite_info = $invite_info;
    }
}
