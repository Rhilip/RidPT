<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 7/28/2019
 * Time: 5:04 PM
 */

namespace apps\components;

use apps\models;
use apps\libraries\Mailer;
use apps\libraries\Constant;

use Rid\Http\View;
use Rid\Base\Component;
use Rid\Helpers\JWTHelper;
use Rid\Utils\ClassValueCacheUtils;

use RuntimeException;

class Site extends Component
{
    use ClassValueCacheUtils;

    protected $cur_user;

    protected $users = [];
    protected $torrents = [];
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
        $this->torrents = [];
        $this->map_username_to_id = [];
    }

    protected static function getStaticCacheNameSpace(): string
    {
        return 'Cache:site';
    }

    public function getBanIpsList(): array
    {
        return static::getStaticCacheValue('ip_ban_list', function () {
            return app()->pdo->createCommand('SELECT `ip` FROM `ban_ips`')->queryColumn();
        }, 86400);
    }

    public function getTorrent($tid)
    {
        if (array_key_exists($tid, $this->torrents)) {
            $torrent = $this->torrents[$tid];
        } else {
            $torrent = new models\Torrent($tid);  // TODO Handing if this user id does not exist
            $this->torrents[$tid] = $torrent;
        }
        return $torrent;
    }


    /**
     * @param $uid
     * @return models\User|bool return False means this user is not exist
     */
    public function getUser($uid)
    {
        if (array_key_exists($uid, $this->users)) {
            $user = $this->users[$uid];
        } else {
            $user = new models\User($uid);  // TODO Handing if this user id does not exist
            $this->users[$uid] = $user;
        }
        return $user;
    }

    /**
     * @param $username
     * @return models\User|bool
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
     * @param bool $flush
     * @return models\User|bool return False means this user is anonymous
     */
    public function getCurUser($grant = 'cookies', $flush = false)
    {
        if (is_null($this->cur_user) || $flush) {
            $this->cur_user = $this->loadCurUser($grant);
        }
        return $this->cur_user;
    }

    /**
     * @param string $grant
     * @return models\User|boolean
     */
    protected function loadCurUser($grant = 'cookies')
    {
        $user_id = false;
        if ($grant == 'cookies') $user_id = $this->loadCurUserIdFromCookies();
        elseif ($grant == 'passkey') $user_id = $this->loadCurUserIdFromPasskey();

        if ($user_id !== false && is_int($user_id) && $user_id > 0) {
            $user_id = intval($user_id);
            $curuser = $this->getUser($user_id);
            if ($curuser->getStatus() !== models\User::STATUS_DISABLED)  // user status shouldn't be disabled
                return $curuser;
        }

        return false;
    }

    protected function loadCurUserIdFromCookies()
    {
        $user_session = app()->request->cookie(Constant::cookie_name);
        if (is_null($user_session)) return false;  // quick return when cookies is not exist

        $payload = JWTHelper::decode($user_session);
        if ($payload === false) return false;
        if (!isset($payload['jti']) || !isset($payload['user_id'])) return false;

        // Check if user lock access ip ?
        if (isset($payload['secure_login_ip'])) {
            $now_ip_crc = sprintf('%08x', crc32(app()->request->getClientIp()));
            if (strcasecmp($payload['secure_login_ip'], $now_ip_crc) !== 0) return false;
        }

        // Verity $jti is force expired or not by checking mapUserSessionToId
        $expired_check = app()->redis->zScore(Constant::mapUserSessionToId, $payload['jti']);
        if ($expired_check === false) {  // session is not see in Zset Cache (may lost or first time init), load from database ( Lazy load... )
            $uid = app()->pdo->createCommand('SELECT `uid` FROM `user_session_log` WHERE sid = :sid AND `expired` != 1 LIMIT 1')->bindParams([
                'sid' => $payload['jti']
            ])->queryScalar();
            app()->redis->zAdd(Constant::mapUserSessionToId, $uid ?: 0, $payload['jti']);  // Store 0 if session -> uid is invalid
            if ($uid === false) return false;  // this session is not exist or marked as expired
        } elseif ($expired_check != $payload['user_id']) return false;    // may return (double) 0 , which means already make invalid ; or it check if user obtain this session (may Overdesign)

        // Check if user want secure access but his environment is not secure
        if (!app()->request->isSecure() &&                     // if User requests is not secure , then
            ((isset($payload['ssl']) && $payload['ssl'] &&     //   if User want secure access
              config('security.ssl_login') > 0          //      and if Our site support ssl feature
             ) || config('security.ssl_login') > 1)) {  //   or if  Our site FORCE enabled ssl feature
            app()->response->redirect(str_replace('http://', 'https://', app()->request->fullUrl()));
            app()->response->setHeader('Strict-Transport-Security', 'max-age=1296000; includeSubDomains');
        }

        return $payload['user_id'];
    }

    protected function loadCurUserIdFromPasskey()
    {
        $passkey = app()->request->get('passkey');
        if (is_null($passkey)) return false;

        $user_id = app()->redis->zScore(Constant::mapUserPasskeyToId, $passkey);
        if (false === $user_id) {
            $user_id = app()->pdo->createCommand('SELECT `id` FROM `users` WHERE `passkey` = :passkey LIMIT 1;')->bindParams([
                'passkey' => $passkey
            ])->queryScalar() ?: 0;
            app()->redis->zAdd(Constant::mapUserPasskeyToId, $user_id, $passkey);
        }

        return $user_id > 0 ? $user_id : false;
    }

    public function getTorrentFileLoc($tid)
    {
        return app()->getPrivatePath('torrents') . DIRECTORY_SEPARATOR . $tid . '.torrent';
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
            $cats_raw = app()->pdo->createCommand('SELECT * FROM `categories` WHERE `id` > 0 ORDER BY `full_path`')->queryAll();

            $cats = [];
            foreach ($cats_raw as $cat_raw) $cats[$cat_raw['id']] = $cat_raw;
            return $cats;
        }, 86400);
    }

    public static function CategoryDetail($cat_id): array
    {
        return static::ruleCategory()[$cat_id];
    }

    public static function ruleCanUsedCategory(): array
    {
        return array_filter(static::ruleCategory(), function ($cat) {
            return $cat['enabled'] = 1;
        });
    }

    public static function ruleQuality($quality): array
    {
        if (!in_array($quality, array_keys(self::getQualityTableList()))) throw new RuntimeException('Unregister quality : ' . $quality);
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
            return app()->site->getCurUser()->getClass() >= $team['class_require'];
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
