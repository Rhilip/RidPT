<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 7/28/2019
 * Time: 5:04 PM
 */

namespace apps\components;

use apps\libraries\Constant;
use Rid\Base\Component;

use apps\models\User;
use Rid\Utils\ClassValueCacheUtils;

class Site extends Component
{
    use ClassValueCacheUtils;

    protected $cur_user;

    protected $users = [];
    protected $map_username_to_id = [];

    public function onRequestBefore()
    {
        parent::onRequestBefore();
        $this->cur_user = null;
        $this->users = [];
        $this->map_username_to_id = [];
    }

    /**
     * @param $uid
     * @return User|bool return False means this user is not exist
     */
    public function getUser($uid)
    {
        if (array_key_exists($uid, $this->users)) {
            $user = $this->users[$uid];
        } else {
            $user = new User($uid);  // TODO Handing if this user id does not exist
            $this->users[$uid] = $user;
        }
        return $user;
    }

    /**
     * @param $username
     * @return User|bool
     */
    public function getUserByUserName($username)
    {
        if (array_key_exists($username, $this->map_username_to_id)) {
            $uid = $this->map_username_to_id[$username];
        } else {
            $uid = app()->redis->hGet(Constant::mapUsernameToId, $username);
            if (false === $uid) {
                $uid = app()->pdo->createCommand('SELECT id FROM `users` WHERE LOWER(`username`) = LOWER(:uname) LIMIT 1;')->bindParams([
                    'uname' => $username
                ])->queryScalar() ?: 0;  // 0 means this username is not exist ???
                app()->redis->hSet(Constant::mapUsernameToId, $username, $uid);
                $this->map_username_to_id[$username] = $uid;
            }
        }

        return $this->getUser($uid);
    }

    /**
     * @param string $grant
     * @return User|bool return False means this user is anonymous
     */
    public function getCurUser($grant = 'cookies')
    {
        if (is_null($this->cur_user)) {
            $this->cur_user = $this->loadCurUser($grant);
        }
        return $this->cur_user;
    }

    /**
     * @param string $grant
     * @return User|boolean
     */
    protected function loadCurUser($grant = 'cookies')
    {
        $user_id = false;
        if ($grant == 'cookies') $user_id = $this->loadCurUserFromCookies();
        elseif ($grant == 'passkey') $user_id = $this->loadCurUserFromPasskey();
        // elseif ($grant == 'oath2') $user_id = $this->loadCurUserFromOAth2();

        if ($user_id !== false) {
            $user_id = intval($user_id);
            return $this->getUser($user_id);
        }

        return false;
    }

    protected function loadCurUserFromCookies()
    {
        $user_session_id = app()->request->cookie(Constant::cookie_name);
        $user_id = app()->redis->zScore(Constant::mapUserSessionToId, $user_session_id);
        if (false === $user_id) {
            // First check cache
            if (false === app()->redis->zScore(Constant::invalidUserSessionZset, $user_session_id)) {
                // check session from database to avoid lost
                $user_id = app()->pdo->createCommand('SELECT `uid` FROM `user_session_log` WHERE `sid` = :sid LIMIT 1;')->bindParams([
                    'sid' => $user_session_id
                ])->queryScalar();
                if (false === $user_id) {  // This session is not exist
                    app()->redis->zAdd(Constant::invalidUserSessionZset, time() + 86400, $user_session_id);
                } else {  // Remember it
                    app()->redis->zAdd(Constant::mapUserSessionToId, $user_id, $user_session_id);
                }
            }
        }

        return $user_id;
    }

    protected function loadCurUserFromPasskey()
    {
        $passkey = app()->request->get('passkey');
        $user_id = app()->redis->zScore(Constant::mapUserPasskeyToId, $passkey);
        if (false === $user_id) {
            if (app()->redis->zScore(Constant::invalidUserPasskeyZset, $passkey) !== false) {
                $user_id = app()->pdo->createCommand('SELECT `id` FROM `users` WHERE `passkey` = :passkey LIMIT 1;')->bindParams([
                    'passkey' => $passkey
                ])->queryScalar();
                if (false === $user_id) {
                    app()->redis->zAdd(Constant::invalidUserPasskeyZset, time() + 600, $passkey);
                } else {
                    app()->redis->zAdd(Constant::mapUserPasskeyToId, $user_id, $passkey);
                }
            }
        }

        return $user_id;
    }
}
