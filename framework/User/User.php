<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/1/8
 * Time: 19:30
 */

namespace Mix\User;

use Mix\Base\Component;
use Mix\Helpers\StringHelper;

class User extends Component implements UserInterface
{
    use UserTrait;

    public $sessionSaveKey = 'USER_SESSION';

    // Cookie
    public $cookieName = 'rid';
    public $cookieExpires = 0x7fffffff;
    public $cookiePath = '/';
    public $cookieDomain = '';
    public $cookieSecure = false;
    public $cookieHttpOnly = false;

    // Key of User Session
    protected $_userKey;
    protected $_userSessionId;
    protected $_userIdLength = 26;

    private $anonymous = false;

    public function onRequestBefore()
    {
        parent::onRequestBefore();
        $this->loadUserFromCookies();
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

    public function loadUserFromCookies()
    {
        $this->_userSessionId = \Mix::app()->request->cookie($this->cookieName);
        $userId = app()->redis->zScore($this->sessionSaveKey, $this->_userSessionId);

        if ($userId) {
            $this->loadUserContentById($userId);
        } else {
            $this->anonymous = true;
        }
    }

    public function createUserSessionId($userId)
    {
        $this->_userSessionId = StringHelper::getRandomString($this->_userIdLength);
        app()->redis->zAdd($this->sessionSaveKey, $userId, $this->_userSessionId);
        app()->cookie->set($this->cookieName, $this->_userSessionId);
    }

    public function deleteUserThisSession()
    {
        $success = app()->redis->zRem($this->sessionSaveKey, $this->_userSessionId);
        return $success ? true : false;
    }

    // 删除
    public function delete($name)
    {
        $success = app()->redis->zRem($this->sessionSaveKey, $name);
        return $success ? true : false;
    }

    // 获取SessionId
    public function getSessionId()
    {
        return $this->_userSessionId;
    }

}
