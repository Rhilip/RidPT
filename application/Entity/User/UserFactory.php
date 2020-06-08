<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 1/27/2020
 * Time: 2020
 */

declare(strict_types=1);

namespace App\Entity\User;

use App\Exceptions\NotExistException;

class UserFactory
{
    public const mapUsernameToId = 'Map:hash:user_username_to_user_id';
    public const mapUserPasskeyToId = 'Map:zset:user_passkey_to_user_id';  // (double) 0 means invalid
    public const mapUserSessionToId = 'Map:zset:user_session_to_user_id';  // (double) 0 means invalid

    public function getUserById($uid): User
    {
        if (!context()->has('user.' . $uid)) {
            try {
                $user = new User($uid);
            } catch (NotExistException $e) {
                $user = false;
            }
            return context()->set('user.' . $uid, $user);
        }
        return context()->get('user.' . $uid);
    }

    public function getUserIdBySession($session): int
    {
        // session is not see in Zset Cache (may lost or first time init), load from database ( Lazy load... )
        if (false === $uid = container()->get('redis')->zScore(self::mapUserSessionToId, $session)) {
            $uid = container()->get('dbal')->prepare('SELECT `uid` FROM sessions WHERE session = :sid AND `expired` != 1 LIMIT 1')->bindParams([
                'sid' => $session
            ])->fetchScalar() ?: 0;   // this session is not exist or marked as expired
            container()->get('redis')->zAdd(self::mapUserSessionToId, $uid, $session);
        }
        return (int)$uid;
    }

    public function getUserIdByPasskey($passkey): int
    {
        if (false === $uid = container()->get('redis')->zScore(self::mapUserPasskeyToId, $passkey)) {
            $uid = container()->get('dbal')->prepare('SELECT id FROM `users` WHERE `passkey` = :passkey')->bindParams([
                'passkey' => $passkey
            ])->fetchScalar() ?: 0;
            container()->get('redis')->zAdd(self::mapUserPasskeyToId, $uid, $passkey);
        }
        return (int)$uid;
    }

    public function getUserByPasskey($passkey)
    {
        $uid = $this->getUserIdByPasskey($passkey);
        return $this->getUserById($uid);
    }

    public function getUserByUsername($username)
    {
        if (false === $uid = container()->get('redis')->hGet(self::mapUsernameToId, $username)) {
            $uid = container()->get('dbal')->prepare('SELECT id FROM `users` WHERE LOWER(`username`) = LOWER(:uname) LIMIT 1;')->bindParams([
                'uname' => $username
            ])->fetchScalar() ?: 0;  // 0 means this username is not exist ???
            container()->get('redis')->hSet(self::mapUsernameToId, $username, $uid);
        }
        return $this->getUserById($uid);
    }
}
