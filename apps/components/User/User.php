<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/1/8
 * Time: 19:30
 */

namespace apps\components\User;

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
    private $bookmark_list = null;

    public function onRequestBefore()
    {
        parent::onRequestBefore();
        $this->class = 0;
        $this->anonymous = true;
        $this->bookmark_list = null;
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

    public function getBookmarkList()
    {
        if (!is_null($this->bookmark_list))
            return $this->bookmark_list;

        $bookmaks = app()->redis->get('User:' . $this->id . ':bookmark_array');
        if ($bookmaks === false) {
            $bookmaks = app()->pdo->createCommand('SELECT `tid` FROM `bookmarks` WHERE `uid` = :uid')->bindParams([
                'uid' => $this->id
            ])->queryColumn() ?: [0];
            app()->redis->set('User:' . $this->id . ':bookmark_array', $bookmaks, 132800);
        }

        $this->bookmark_list = $bookmaks;  // Store in avg to reduce the cache call
        return $bookmaks;
    }

    public function inBookmarkList($tid = null)
    {
        return in_array($tid, $this->getBookmarkList());
    }

    public function isPrivilege($require_class)
    {
        if (is_string($require_class)) {
            $require_class = app()->config->get('authority.' . $require_class);
        }

        return $this->class >= $require_class;
    }
}
