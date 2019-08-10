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

use Exception;
use Firebase\JWT\ExpiredException;
use Rid\Http\View;
use Rid\Base\Component;
use Rid\Utils\ClassValueCacheUtils;

use Firebase\JWT\JWT;
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
     * @return models\User|bool return False means this user is anonymous
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
     * @return models\User|boolean
     */
    protected function loadCurUser($grant = 'cookies')
    {
        $user_id = false;
        if ($grant == 'cookies') $user_id = $this->loadCurUserIdFromCookies();
        elseif ($grant == 'passkey') $user_id = $this->loadCurUserIdFromPasskey();
        // elseif ($grant == 'oath2') $user_id = $this->loadCurUserIdFromOAth2();

        if ($user_id !== false) {
            $user_id = intval($user_id);
            $curuser = $this->getUser($user_id);
            if ($curuser->getStatus() !== models\User::STATUS_DISABLED)  // user status shouldn't be disabled
                return $this->getUser($user_id);
        }

        return false;
    }

    protected function loadCurUserIdFromCookies()
    {
        $timenow = time();
        $user_session_id = app()->request->cookie(Constant::cookie_name);
        if (is_null($user_session_id)) return false;  // quick return when cookies is not exist

        $key = env('APP_SECRET_KEY');

        try {
            $decoded = JWT::decode($user_session_id, $key, ['HS256']);
        } catch (Exception $e) {
            if ($e instanceof ExpiredException) {  // Lazy Expired Check .....
                // Since in this case , we can't get payload with jti information directly, we should manually decode jwt content
                list($headb64, $bodyb64, $cryptob64) = explode('.', $user_session_id);
                $payload = JWT::jsonDecode(JWT::urlsafeB64Decode($bodyb64));
                $jti = $payload->jti ?? '';
                if ($jti && strlen($jti) === 64) {
                    app()->redis->zAdd(Constant::invalidUserSessionZset, $timenow + 86400, $jti);
                    app()->pdo->createCommand('UPDATE `user_session_log` SET `expired` = 1 WHERE `sid` = :sid')->bindParams([
                        'sid' => $jti
                    ])->execute();
                }
            }
            app()->session->set('jwt_error_msg', $e->getMessage());  // Store error msg
            return false;
        }

        $decoded_array = (array)$decoded;  // jwt valid data in array

        if (!isset($decoded_array['jti'])) return false;

        // Check if user lock access ip ?
        if (isset($decoded_array['secure_login_ip'])) {
            $now_ip_crc = sprintf('%08x', crc32(app()->request->getClientIp()));
            if (strcasecmp($decoded_array['secure_login_ip'], $now_ip_crc) !== 0) return false;
        }

        // Check if user want secure access but his environment is not secure
        if (isset($decoded_array['ssl']) && $decoded_array['ssl'] && // User want secure access
            !app()->request->isSecure() // User requests is not secure
            // TODO our site support ssl feature
        ) {
            app()->response->redirect(str_replace('http://', 'https://', app()->request->fullUrl()));
            app()->response->setHeader('Strict-Transport-Security', 'max-age=1296000; includeSubDomains');
        }

        // Verity $jti is force expired or not ?
        $jti = $decoded_array['jti'];
        if ($jti !== app()->session->get('jti')) { // Not Match Session record
            if (false === app()->redis->zScore(Constant::validUserSessionZset, $jti)) {  // Not Record in valid cache
                // if this $jti not in valid cache , then check invalid cache
                if (app()->redis->zScore(Constant::invalidUserSessionZset, $jti) !== false) {
                    return false;   // This $jti has been marked as invalid
                } else {  // Invalid cache still not hit, then check $jti from database to avoid lost
                    $exist_jti = app()->pdo->createCommand('SELECT `id` FROM `user_session_log` WHERE `sid` = :sid AND `expired` = 0 LIMIT 1;')->bindParams([
                        'sid' => $jti
                    ])->queryScalar();

                    if (false === $exist_jti) {  // Absolutely This $jti is not exist or expired
                        app()->redis->zAdd(Constant::invalidUserSessionZset, $timenow + 86400, $jti);
                        return false;
                    }
                }

                app()->redis->zAdd(Constant::validUserSessionZset, $decoded_array['exp'] ?? $timenow + 43200, $jti);  // Store in valid cache
            }
            app()->session->set('jti', $jti);  // Store the $jti value in session so we can visit $jti in other place
        }

        return $decoded_array['user_id'] ?? false;
    }

    protected function loadCurUserIdFromPasskey()
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
            return app()->pdo->createCommand('SELECT * FROM `categories` WHERE `id` > 0 ORDER BY `full_path`')->queryAll();
        }, 86400);
    }

    public static function CategoryDetail($cat_id): array
    {
        return static::getStaticCacheValue('torrent_category_' . $cat_id, function () use ($cat_id) {
            return app()->pdo->createCommand('SELECT * FROM `categories` WHERE id= :cid LIMIT 1;')->bindParams([
                'cid' => $cat_id
            ])->queryOne();
        }, 86400);
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
