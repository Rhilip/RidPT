<?php

namespace apps\models;

use Rid\Utils\AttributesImportUtils;
use Rid\Utils\ClassValueCacheUtils;

use apps\libraries\Constant;

class User
{

    use AttributesImportUtils;
    use ClassValueCacheUtils;

    // User class
    public const ROLE_PEASANT = 0;
    public const ROLE_USER = 1;
    public const ROLE_POWER_USER = 2;
    public const ROLE_ELITE_USER = 3;
    public const ROLE_CRAZY_USER = 4;
    public const ROLE_INSANE_USER = 5;
    public const ROLE_VETERAN_USER = 6;
    public const ROLE_EXTREME_USER = 7;
    public const ROLE_ULTIMATE_USER = 8;
    public const ROLE_MASTER_USER = 9;   # The max level that user can reached if they reached the level setting
    public const ROLE_TEMP_VIP = 10;    # The max level that user can reached via bonus exchange

    // Contributor class
    public const ROLE_VIP = 20;
    public const ROLE_RETIREE = 30;

    // Uploader class
    public const ROLE_UPLOADER = 40;
    public const ROLE_HELPER = 50;

    // Administrator class
    public const ROLE_FORUM_MODERATOR = 60;
    public const ROLE_MODERATOR = 70;
    public const ROLE_ADMINISTRATOR = 80;
    public const ROLE_SYSOP = 90;
    public const ROLE_STAFFLEADER = 100;

    public const ROLE = [
        self::ROLE_PEASANT => 'PEASANT',
        self::ROLE_USER => 'USER',
        self::ROLE_POWER_USER => 'POWER_USER',
        self::ROLE_ELITE_USER => 'ELITE_USER',
        self::ROLE_CRAZY_USER => 'CRAZY_USER',
        self::ROLE_INSANE_USER => 'INSANE_USER',
        self::ROLE_VETERAN_USER => 'VETERAN_USER',
        self::ROLE_EXTREME_USER => 'EXTREME_USER',
        self::ROLE_ULTIMATE_USER => 'ULTIMATE_USER',
        self::ROLE_MASTER_USER => 'MASTER_USER',
        self::ROLE_TEMP_VIP => 'TEMP_VIP',

        self::ROLE_VIP => 'VIP',
        self::ROLE_RETIREE => 'RETIREE',

        self::ROLE_UPLOADER => 'UPLOADER',
        self::ROLE_HELPER => 'HELPER',

        self::ROLE_FORUM_MODERATOR => 'FORUM_MODERATOR',
        self::ROLE_MODERATOR => 'MODERATOR',
        self::ROLE_ADMINISTRATOR => 'ADMINISTRATOR',
        self::ROLE_SYSOP => 'SYSOP',
        self::ROLE_STAFFLEADER => 'STAFFLEADER'
    ];

    // User Status
    public const STATUS_BANNED = 'banned';
    public const STATUS_PENDING = 'pending';
    public const STATUS_PARKED = 'parked';
    public const STATUS_CONFIRMED = 'confirmed';

    private $id;
    private $username;
    private $email;
    private $status;
    private $class = 0;
    private $passkey;
    private $avatar;
    private $lang;

    private $create_at;
    private $last_login_at;
    private $last_access_at;
    private $last_upload_at;
    private $last_download_at;
    private $last_connect_at;

    private $register_ip;
    private $last_login_ip;
    private $last_access_ip;
    private $last_tracker_ip;

    private $uploaded;
    private $true_uploaded;
    private $downloaded;
    private $true_downloaded;
    private $seedtime;
    private $leechtime;

    private $bonus_seeding;
    private $bonus_invite;
    private $bonus_other;

    private $invites;

    protected $infoCacheKey;

    public function getBonus() {
        return $this->bonus_seeding + $this->bonus_invite + $this->bonus_other;
    }

    /**
     * @return mixed
     */
    public function getBonusSeeding()
    {
        return $this->bonus_seeding;
    }

    /**
     * @return mixed
     */
    public function getBonusInvite()
    {
        return $this->bonus_invite;
    }

    /**
     * @return mixed
     */
    public function getBonusOther()
    {
        return $this->bonus_other;
    }

    protected function getCacheNameSpace(): string
    {
        return Constant::userContent($this->id);
    }

    public function __construct($id = null)
    {
        $this->infoCacheKey = Constant::userContent($id);
        $self = app()->redis->hGetAll($this->infoCacheKey);
        if (empty($self) || !isset($self['id'])) {
            if (app()->redis->zScore(Constant::invalidUserIdZset, $id) === false) {
                $self = app()->pdo->createCommand("SELECT * FROM `users` WHERE id = :id LIMIT 1;")->bindParams([
                    "id" => $id
                ])->queryOne();
                if (false === $self) {
                    app()->redis->zAdd(Constant::invalidUserIdZset, time() + 3600, $id);
                } else {
                    app()->redis->hMset($this->infoCacheKey, $self);
                    app()->redis->expire($this->infoCacheKey, 15 * 60);  // Cache This User Detail for 15 minutes
                }
            } else {
                $self = false;  // It means this user id is invalid ( And already checked in last 60 minutes )
            }
        }

        if ($self === false) return false;

        $this->importAttributes($self);
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param bool $raw
     * @return mixed
     */
    public function getClass($raw = false)
    {
        return $raw ? $this->class : self::ROLE[$this->class];
    }

    /** TODO use gravatar
     * @return mixed
     */
    public function getAvatar()
    {
        if ($this->avatar == '') {
            $this->avatar = '/static/avatar/default_avatar.jpg';
        }

        return $this->avatar;
    }

    /**
     * @return mixed
     */
    public function getCreateAt()
    {
        return $this->create_at;
    }

    /**
     * @return mixed
     */
    public function getLastLoginAt()
    {
        return $this->last_login_at;
    }

    /**
     * @return mixed
     */
    public function getLastAccessAt()
    {
        return $this->last_access_at;
    }

    /**
     * @return mixed
     */
    public function getLastUploadAt()
    {
        return $this->last_upload_at;
    }

    /**
     * @return mixed
     */
    public function getLastDownloadAt()
    {
        return $this->last_download_at;
    }

    /**
     * @return mixed
     */
    public function getLastConnectAt()
    {
        return $this->last_connect_at;
    }

    /**
     * @param bool $readable
     * @return mixed
     */
    public function getRegisterIp($readable = true)
    {
        return ($this->register_ip && $readable) ? inet_ntop($this->register_ip) : $this->register_ip;
    }

    /**
     * @param bool $readable
     * @return mixed
     */
    public function getLastLoginIp($readable = true)
    {
        return ($this->last_login_ip && $readable) ? inet_ntop($this->last_login_ip) : $this->last_login_ip;
    }

    /**
     * @param bool $readable
     * @return mixed
     */
    public function getLastAccessIp($readable = true)
    {
        return ($this->last_access_ip && $readable) ? inet_ntop($this->last_access_ip) : $this->last_access_ip;
    }

    /**
     * @param bool $readable
     * @return mixed
     */
    public function getLastTrackerIp($readable = true)
    {
        return ($this->last_tracker_ip && $readable) ? inet_ntop($this->last_tracker_ip) : $this->last_tracker_ip;
    }

    public function getUploaded(): int
    {
        return $this->uploaded;
    }

    public function getRealUploaded(): int
    {
        return $this->getCacheValue('true_uploaded', function () {
            return app()->pdo->createCommand('SELECT SUM(`true_uploaded`) FROM `snatched` WHERE `user_id` = :uid')->bindParams([
                    "uid" => $this->id
                ])->queryScalar() ?? 0;
        });
    }

    public function getDownloaded(): int
    {
        return $this->downloaded;
    }

    public function getRealDownloaded()
    {
        return $this->getCacheValue('true_downloaded', function () {
            return app()->pdo->createCommand('SELECT SUM(`true_downloaded`) FROM `snatched` WHERE `user_id` = :uid')->bindParams([
                    "uid" => $this->id
                ])->queryScalar() ?? 0;
        });
    }

    public function getRatio()
    {
        $uploaded = $this->getUploaded();
        $download = $this->getDownloaded();
        if ($download == 0 && $uploaded == 0) {
            return '---';
        } elseif ($download == 0) {
            return 'Inf.';
        } else {
            return $uploaded / $download;
        }
    }

    public function getRealRatio()
    {
        $uploaded = $this->getRealUploaded();
        $download = $this->getRealDownloaded();
        if ($download == 0 && $uploaded == 0) {
            return '---';
        } elseif ($download == 0) {
            return 'Inf.';
        } else {
            return $uploaded / $download;
        }
    }

    public function getSeedtime()
    {
        return $this->seedtime;
    }

    public function getLeechtime()
    {
        return $this->leechtime;
    }

    public function getTimeRatio()
    {
        $seedtime = $this->seedtime;
        $leechtime = $this->leechtime;
        if ($leechtime == 0 && $seedtime == 0) {
            return '---';
        } elseif ($leechtime == 0) {
            return 'Inf.';
        } else {
            return $seedtime / $leechtime;
        }
    }

    private function getPeerStatus($seeder = null)
    {
        $peer_status = $this->getCacheValue('peer_count', function () {
            $peer_count = app()->pdo->createCommand("SELECT `seeder`, COUNT(id) FROM `peers` WHERE `user_id` = :uid GROUP BY seeder")->bindParams([
                'uid' => $this->id
            ])->queryAll() ?: [];
            return array_merge(['yes' => 0, 'no' => 0, 'partial' => 0], $peer_count);
        });

        return $seeder ? (int)$peer_status[$seeder] : $peer_status;
    }

    public function getActiveSeed()
    {
        return $this->getPeerStatus('yes');
    }

    public function getActiveLeech()
    {
        return $this->getPeerStatus('no');
    }

    public function getActivePartial()
    {
        return $this->getPeerStatus('partial');
    }

    /**
     * @return mixed
     */
    public function getLang()
    {
        return $this->lang;
    }

    /**
     * @return mixed
     */
    public function getPasskey()
    {
        return $this->passkey;
    }

    /**
     * @return mixed
     */
    public function getInvites()
    {
        return $this->invites ?? 0;
    }

    /**
     * @return mixed
     */
    public function getTempInvitesSum()
    {
        return array_sum(array_map(function ($d) {
            return $d['total'] - $d['used'];
        }, $this->getTempInviteDetails()));
    }

    /**
     * @return array
     */
    public function getTempInviteDetails()
    {
        return $this->getCacheValue('temp_invites_details', function () {
                return app()->pdo->createCommand('SELECT * FROM `user_invitations` WHERE `user_id` = :uid AND (`total`-`used`) > 0 AND `expire_at` > NOW() ORDER BY `expire_at` ASC')->bindParams([
                    "uid" => app()->site->getCurUser()->getId()
                ])->queryAll() ?: [];
            }) ?? [];
    }

    public function getPendingInvites()
    {
        return $this->getCacheValue('pending_invites', function () {
            return app()->pdo->createCommand('SELECT * FROM `invite` WHERE inviter_id = :uid AND expire_at > NOW() AND used = 0')->bindParams([
                'uid' => $this->id
            ])->queryAll();
        });
    }

    public function getInvitees()
    {
        return $this->getCacheValue('invitees', function () {
            return app()->pdo->createCommand('SELECT id,username,email,status,class,uploaded,downloaded FROM `users` WHERE `invite_by` = :uid')->bindParams([
                'uid' => $this->id
            ])->queryAll();
        });
    }

    public function getBookmarkList()
    {
        return $this->getCacheValue('bookmark_list', function () {
            return app()->pdo->createCommand('SELECT `tid` FROM `bookmarks` WHERE `uid` = :uid')->bindParams([
                'uid' => $this->id
            ])->queryColumn() ?: [];
        });
    }

    public function getUnreadMessageCount()
    {
        return $this->getCacheValue('unread_message_count', function () {
            return app()->pdo->createCommand("SELECT COUNT(`id`) FROM `messages` WHERE receiver = :uid AND unread = 'no'")->bindParams([
                'uid' => $this->id
            ])->queryScalar();
        });
    }

    public function getInboxMessageCount()
    {
        return $this->getCacheValue('inbox_count', function () {
            return app()->pdo->createCommand('SELECT COUNT(`id`) FROM `messages` WHERE `receiver` = :uid')->bindParams([
                'uid' => $this->id
            ])->queryScalar();
        });
    }

    public function getOutboxMessageCount()
    {
        return $this->getCacheValue('outbox_count', function () {
            return app()->pdo->createCommand('SELECT COUNT(`id`) FROM `messages` WHERE `sender` = :uid')->bindParams([
                'uid' => $this->id
            ])->queryScalar();
        });
    }

    public function inBookmarkList($tid = null)
    {
        return in_array($tid, $this->getBookmarkList());
    }

    public function isPrivilege($require_class)
    {
        if (is_string($require_class)) {
            $require_class = config('authority.' . $require_class, false) ?: 1;
        }

        return $this->class >= $require_class;
    }

    public function getSessionId() {
        return app()->request->cookie(Constant::cookie_name);
    }

    public function deleteUserThisSession()
    {
        $user_session_id = app()->request->cookie(Constant::cookie_name);
        $success = app()->redis->zRem(Constant::mapUserSessionToId, $user_session_id);
        app()->pdo->createCommand('UPDATE `user_session_log` SET `expired` = 1 WHERE sid = :sid')->bindParams([
            'sid' => $user_session_id
        ])->execute();
        app()->cookie->delete(Constant::cookie_name);
        return $success ? true : false;
    }
}
