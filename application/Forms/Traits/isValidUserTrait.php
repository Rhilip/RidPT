<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/6/2020
 * Time: 10:46 PM
 */

declare(strict_types=1);

namespace App\Forms\Traits;

use App\Entity\User\User;

trait isValidUserTrait
{
    private ?User $user;

    protected function isValidUser()
    {
        $uid = $this->getUserId();
        $user = container()->get(\App\Entity\User\UserFactory::class)->getUserById($uid);
        if (false === $user) {
            $this->buildCallbackFailMsg('User', 'The user id (' . $uid . ') is not exist in our database');
        }

        $this->user = $user;
    }

    abstract public function getUserId(): int;

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }
}
