<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/7/2020
 * Time: 12:15 PM
 */

declare(strict_types=1);

namespace App\Forms\Traits;

use App\Entity\User\User;

trait InviteCheckTrait
{
    /** @noinspection PhpUnused */
    protected function isInviteSystemOpen()
    {
        if (config('base.enable_invite_system') != true) {
            $this->buildCallbackFailMsg('InviteSystemOpen', 'The invite system isn\'t open in this site.');
        }
    }

    /** @noinspection PhpUnused */
    protected function canInvite()
    {
        // if user have enough invite number
        if ($this->getInvitesCount() <= 0) {
            $this->buildCallbackFailMsg('Invitation qualification', 'No enough invite qualification');
            return;
        }
    }

    /** @noinspection PhpUnused */
    protected function checkInviteInterval()
    {
        if (!container()->get('auth')->getCurUser()->isPrivilege('pass_invite_interval_check')) {
            $count = container()->get('pdo')->prepare([
                ['SELECT COUNT(`id`) FROM `invite` WHERE `create_at` > DATE_SUB(NOW(),INTERVAL :wait_second SECOND) ', 'params' => ['wait_second' => config('invite.interval')]],
                ['AND `used` = 0', 'if' => !config('invite.force_interval')]
            ])->queryScalar();
            if ($count > 0) {
                $this->buildCallbackFailMsg('Invitation interval', 'Hit invitation interval, please wait');
            }
        }
    }

    public function getInvitesCount()
    {
        /** @var User $user */
        $user = $this->getUser();
        return $user->getTotalInvites();
    }
}
