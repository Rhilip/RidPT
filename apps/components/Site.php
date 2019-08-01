<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 7/28/2019
 * Time: 5:04 PM
 */

namespace apps\components;

use apps\models\User;
use apps\libraries\Mailer;
use apps\libraries\Constant;

use Rid\Http\View;
use Rid\Base\Component;
use Rid\Utils\ClassValueCacheUtils;

class Site extends Component
{
    use ClassValueCacheUtils;

    protected $cur_user;

    protected $users = [];
    protected $map_username_to_id = [];

    const LOG_LEVEL_NORMAL = 'normal';
    const LOG_LEVEL_MOD = 'mod';
    const LOG_LEVEL_SYSOP = 'sysop';
    const LOG_LEVEL_LEADER = 'leader';

    public function onRequestBefore()
    {
        parent::onRequestBefore();
        $this->cur_user = null;
        $this->users = [];
        $this->map_username_to_id = [];
    }

    protected static function getStaticCacheNameSpace(): string
    {
        return 'Cache:site';
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
        if (is_null($user_session_id)) return false;  // quick return when cookies is not exist

        if (false === $user_id = app()->redis->zScore(Constant::mapUserSessionToId, $user_session_id)) {
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
            if (app()->redis->zScore(Constant::invalidUserPasskeyZset, $passkey) === false) {
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

    public function writeLog($msg, $level = self::LOG_LEVEL_NORMAL)
    {
        app()->pdo->createCommand('INSERT INTO `site_log`(`create_at`,`msg`, `level`) VALUES (CURRENT_TIMESTAMP, :msg, :level)')->bindParams([
            'msg' => $msg, 'level' => $level
        ])->execute();
    }

    public function sendPM($sender, $receiver, $subject, $msg, $save = 'no', $location = 1)
    {
        app()->pdo->createCommand('INSERT INTO `messages` (`sender`,`receiver`,`add_at`, `subject`, `msg`, `saved`, `location`) VALUES (:sender,:receiver,`CURRENT_TIMESTAMP`,:subject,:msg,:save,:location)')->bindParams([
            'sender' => $sender, 'receiver' => $receiver,
            'subject' => $subject, 'msg' => $msg,
            'save' => $save, 'location' => $location
        ])->execute();

        app()->redis->hDel(Constant::userContent($receiver), 'unread_message_count', 'inbox_count');
        if ($sender != 0) app()->redis->hDel(Constant::userContent($sender), 'outbox_count');
    }

    public function sendEmail($receivers, $subject, $template, $data = [])
    {
        $mail_body = (new View(false))->render($template, $data);
        $mail_sender = Mailer::newInstanceByConfig('libraries.[mailer]');
        $mail_sender->send($receivers, $subject, $mail_body);
    }

    public static function getQualityTableList()
    {
        return [
            'audio' => 'Audio Codec',  // TODO i18n title
            'codec' => 'Codec',
            'medium' => 'Medium',
            'resolution' => 'Resolution'
        ];
    }

    public static function ruleCategory(): array
    {
        return static::getStaticCacheValue('enabled_torrent_category', function () {
            return app()->pdo->createCommand('SELECT * FROM `categories` WHERE `id` > 0 ORDER BY `full_path`')->queryAll();
        }, 86400);
    }

    public static function CategoryDetail($cat_id): array
    {
        return static::getStaticCacheValue('torrent_category_' . $cat_id ,function () use ($cat_id) {
            return app()->pdo->createCommand('SELECT * FROM `categories` WHERE id= :cid LIMIT 1;')->bindParams([
                'cid' => $cat_id
            ])->queryOne();
        },86400);
    }

    public static function ruleCanUsedCategory(): array
    {
        return array_filter(static::ruleCategory(), function ($cat) {
            return $cat['enabled'] = 1;
        });
    }

    public static function ruleQuality($quality): array
    {
        if (!in_array($quality, array_keys(self::getQualityTableList()))) throw new \RuntimeException('Unregister quality : ' . $quality);
        return static::getStaticCacheValue('enabled_quality_' . $quality, function () use ($quality) {
            return app()->pdo->createCommand("SELECT * FROM `quality_$quality` WHERE `id` > 0 AND `enabled` = 1 ORDER BY `sort_index`,`id`")->queryAll();
        }, 86400);
    }

    public static function ruleTeam(): array
    {
        return static::getStaticCacheValue('enabled_teams', function () {
            return app()->pdo->createCommand('SELECT * FROM `teams` WHERE `id` > 0 AND `enabled` = 1 ORDER BY `sort_index`,`id`')->queryAll();
        }, 86400);
    }

    public static function ruleCanUsedTeam(): array
    {
        return array_filter(static::ruleTeam(), function ($team) {
            return app()->site->getCurUser()->getClass(true) >= $team['class_require'];
        });
    }

    public static function rulePinnedTags(): array
    {
        return static::getStaticCacheValue('pinned_tags', function () {
            return app()->pdo->createCommand('SELECT * FROM `tags` WHERE `pinned` = 1 LIMIT 10;')->queryAll();
        }, 86400);
    }

    public static function fetchUserCount(): int
    {
        return app()->pdo->createCommand("SELECT COUNT(`id`) FROM `users`")->queryScalar();
    }
}
