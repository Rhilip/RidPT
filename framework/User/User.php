<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/1/8
 * Time: 19:30
 */

namespace Rid\User;

use Rid\Base\Component;


class User extends Component implements UserInterface
{
    use UserTrait;

    // Cookie
    public $sessionSaveKey = 'User_Map:session_to_id';
    public $cookieName = 'rid';

    // Key of User Session
    protected $_userSessionId;

    // Passkey
    public $passkeyCacheKey = 'User_Map:passkey_to_id';

    private $anonymous = false;

    public function onRequestBefore()
    {
        parent::onRequestBefore();
        $this->anonymous = true;
    }

    public function onRequestAfter()
    {
        parent::onRequestAfter();
    }

    public function isAnonymous()
    {
        return $this->anonymous;
    }

    public function loadUserFromCookies()
    {
        $this->_userSessionId = app()->request->cookie($this->cookieName);
        $userId = app()->redis->zScore($this->sessionSaveKey, $this->_userSessionId);

        if ($userId) {
            $this->loadUserContentById($userId);
            $this->anonymous = false;
        }
    }

    public function loadUserFromPasskey()
    {
        $passkey = app()->request->get('passkey');
        $userId = app()->redis->zScore($this->passkeyCacheKey, $passkey);
        if ($userId == false) {
            $userId = app()->pdo->createCommand('SELECT `id` FROM `users` WHERE `passkey` = :passkey LIMIT 1')->bindParams([
                'passkey' => $passkey
            ])->queryScalar() ?: 0;
            app()->redis->zAdd($this->passkeyCacheKey, $userId, $passkey);
        }
        if ($userId !== 0) {
            $this->loadUserContentById($userId);
            $this->anonymous = false;
        }
    }

    public function deleteUserThisSession()
    {
        $success = app()->redis->zRem($this->sessionSaveKey, $this->_userSessionId);
        app()->pdo->createCommand('UPDATE `users_session_log` SET `expired` = 1 WHERE sid = :sid')->bindParams([
            'sid' => $this->_userSessionId
        ])->execute();
        app()->cookie->delete($this->cookieName);
        return $success ? true : false;
    }

    // 获取SessionId
    public function getSessionId()
    {
        return $this->_userSessionId;
    }

}
