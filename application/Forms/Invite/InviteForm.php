<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/7/2020
 * Time: 8:17 PM
 */

declare(strict_types=1);

namespace App\Forms\Invite;

use App\Enums\Invite\Type as InviteType;
use App\Forms\Traits\CaptchaTrait;
use App\Forms\Traits\InviteCheckTrait;
use App\Forms\Traits\UserRegisterCheckTrait;
use Rid\Utils\Random;
use Rid\Validators\AbstractValidator;
use Rid\Validators\Constraints as AcmeAssert;
use Symfony\Component\Validator\Constraints as Assert;

class InviteForm extends AbstractValidator
{
    use CaptchaTrait;
    use UserRegisterCheckTrait;
    use InviteCheckTrait;

    protected function loadInputMetadata(): Assert\Collection
    {
        $rules = [
            'username' => new Assert\Length(['max' => 12]),
            'email' => new Assert\Email(),
            'invite_type' => new Assert\Choice(InviteType::values())
        ];

        if ($this->getInput('invite_type') == InviteType::TEMPORARILY) {
            $rules['temp_id'] = new AcmeAssert\PositiveInt();
        }

        return new Assert\Collection($rules);
    }

    protected function loadCallbackMetaData(): array
    {
        return [
            'validateCaptcha',
            'isInviteSystemOpen', 'isRegisterSystemOpen', 'isMaxUserReached',
            'isValidUsername', 'isValidEmail',
            'canInvite', 'checkInviteInterval'
        ];
    }

    public function flush(): void
    {
        $is_temporarily_invite = $this->getInput('invite_type') == InviteType::TEMPORARILY;

        container()->get('pdo')->beginTransaction();
        try {
            // Consume the invite number
            if ($is_temporarily_invite) {
                container()->get('pdo')->prepare('UPDATE `user_invitations` SET `used` = `used` + 1 WHERE `id` = :id')->bindParams([
                    'id' => $this->getInput('temp_id')
                ])->execute();
                container()->get('pdo')->prepare('UPDATE `users` SET temp_invites = temp_invites - 1 WHERE id = :id')->bindParams([
                    'id' => container()->get('auth')->getCurUser()->getId()
                ])->execute();
            } else {  // Consume user privilege invite
                container()->get('pdo')->prepare('UPDATE `users` SET `invites` = `invites` - 1 WHERE `id` = :uid')->bindParams([
                    'uid' => container()->get('auth')->getCurUser()->getId()
                ])->execute();
            }

            $invite_hash = $this->insertInviteRecord();
            container()->get('redis')->del('User:' . container()->get('auth')->getCurUser()->getId() . ':base_content');  // flush it's cache

            container()->get('pdo')->commit();

            $invite_link = container()->get('request')->getSchemeAndHttpHost() . '/auth/register?' . http_build_query([
                    'type' => 'invite',
                    'invite_hash' => $invite_hash
                ]);
            container()->get('site')->sendEmail(
                [$this->getInput('email')],
                'Invite To ' . config('base.site_name'),
                'email/user_invite',
                [
                    'username' => $this->getInput('username'),
                    'invite_link' => $invite_link,
                ]
            );
        } catch (\Exception $e) {
            //$invite_status = $e->getMessage();
            container()->get('pdo')->rollback();
        }
    }

    private function insertInviteRecord()
    {
        do { // To make sure this hash is unique !
            $invite_hash = Random::alnum(32);

            $count = container()->get('pdo')->prepare('SELECT COUNT(`id`) FROM `invite` WHERE `hash` = :hash')->bindParams([
                'hash' => $invite_hash
            ])->queryScalar();
        } while ($count != 0);


        container()->get('pdo')->prepare('INSERT INTO `invite` (`inviter_id`,`username`,`invite_type`, `hash`, `create_at`, `expire_at`) VALUES (:inviter_id,:username,:invite_type,:hash,NOW(),DATE_ADD(NOW(),INTERVAL :timeout SECOND))')->bindParams([
            'inviter_id' => container()->get('auth')->getCurUser()->getId(), 'username' => $this->getInput('username'), 'invite_type' => $this->getInput('invite_type'),
            'hash' => $invite_hash, 'timeout' => config('invite.timeout')
        ])->execute();
        return $invite_hash;
    }

    protected function getRegisterType()
    {
        return 'invite';
    }

    protected function getUser()
    {
        return container()->get('auth')->getCurUser();
    }
}
