<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/1/8
 * Time: 19:30
 */

namespace Mix\User;

use Mix\Base\Component;


class User extends Component implements UserInterface
{
    use UserTrait;

    // Cookie
    public $sessionSaveKey = 'SESSION:user_set';
    public $cookieName = 'rid';

    // Key of User Session
    protected $_userSessionId;

    private $anonymous = false;

    public function onRequestBefore()
    {
        parent::onRequestBefore();
        $this->loadUser();
    }

    public function onRequestAfter()
    {
        parent::onRequestAfter();
        app()->redis->disconnect();
    }

    public function isAnonymous()
    {
        return $this->anonymous;
    }

    public function loadUser() {
        // TODO Load User From Passkey in some route , for example '/rss'
        return $this->loadUserFromCookies();
    }

    public function loadUserFromCookies()
    {
        $this->_userSessionId = \Mix::app()->request->cookie($this->cookieName);
        $userId = app()->redis->zScore($this->sessionSaveKey, $this->_userSessionId);

        if ($userId) {
            $this->loadUserContentById($userId);
            $this->anonymous = false;
        } else {
            $this->anonymous = true;
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
