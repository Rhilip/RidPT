<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/7/2020
 * Time: 11:48 AM
 */

declare(strict_types=1);

namespace App\Forms\Invite;

use App\Entity\User\UserFactory;
use App\Forms\Traits\InviteCheckTrait;
use App\Forms\Traits\isValidUserTrait;
use Rid\Validators\AbstractValidator;
use Symfony\Component\Validator\Constraints as Assert;

class IndexForm extends AbstractValidator
{
    use isValidUserTrait;
    use InviteCheckTrait;

    private array $temporary_invitations = [];
    private array $invitees = [];
    private array $pending_invites = [];

    protected function loadInputMetadata(): Assert\Collection
    {
        return new Assert\Collection([]);
    }

    protected function loadCallbackMetaData(): array
    {
        return ['isValidUser', 'isInviteSystemOpen'];
    }

    public function flush(): void
    {
        // Get Invitees Information
        $invitees = container()->get('dbal')->prepare('SELECT id FROM `users` WHERE invite_by = :id')->bindParams([
            'id' => $this->getUserId()
        ])->fetchColumn();
        foreach ($invitees as $invitee) {
            $this->invitees[] = container()->get(UserFactory::class)->getUserById($invitee);
        }

        // Get Pending Information
        $this->pending_invites = container()->get('dbal')->prepare('SELECT * FROM `invite` WHERE inviter_id = :uid AND expire_at > NOW() AND used = 0')->bindParams([
            'uid' => $this->getUserId()
        ])->fetchAll();

        // Get Temporary Invitations Information
        $this->temporary_invitations = container()->get('dbal')->prepare('SELECT * FROM `user_invitations` WHERE `user_id` = :uid AND (`total`-`used`) > 0 AND `expire_at` > NOW() ORDER BY `expire_at` ASC')->bindParams([
            "uid" => $this->getUserId()
        ])->fetchAll() ?: [];

        $temp_invitations_count = array_sum(array_map(function ($d) {
            return $d['total'] - $d['used'];
        }, $this->temporary_invitations));

        if ($temp_invitations_count != $this->getUser()->getTempInvites()) {
            container()->get('dbal')->prepare('UPDATE `users` SET temp_invites = :temp_invites WHERE id = :id')->bindParams([
                'id' => $this->getUserId(), 'temp_invites' => $temp_invitations_count
            ])->execute();
        }
    }

    public function getUserId(): int
    {
        return (int)container()->get('auth')->getCurUser()->getId();
    }

    public function getTemporaryInvitations(): array
    {
        return $this->temporary_invitations;
    }

    public function getInvitees(): array
    {
        return $this->invitees;
    }

    public function getPendingInvites(): array
    {
        return $this->pending_invites;
    }
}
