<?php

namespace App\Models;

use Rid\Utils\AttributesImportUtils;
use Rid\Utils\ClassValueCacheUtils;

use App\Libraries\Constant;

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
    public const STATUS_DISABLED = 'disabled';
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

    private $uploadpos;
    private $downloadpos;

    private $uploaded;
    private $true_uploaded;
    private $downloaded;
    private $true_downloaded;
    private $seedtime;
    private $leechtime;

    private $bonus_seeding;
    private $bonus_other;

    private $invites;

    protected $infoCacheKey;

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
                $self = app()->pdo->createCommand('SELECT id, username, email, status, class, passkey, uploadpos, downloadpos, uploaded, downloaded, seedtime, leechtime, avatar, bonus_seeding, bonus_other, lang, invites FROM `users` WHERE id = :id LIMIT 1;')->bindParams([
                    'id' => $id
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

    public function getId(): int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getClass(): int
    {
        return $this->class;
    }

    public function getPasskey(): string
    {
        return $this->passkey;
    }

    public function getUploadpos(): bool
    {
        return (bool)$this->uploadpos;
    }

    public function getDownloadpos(): bool
    {
        return (bool)$this->downloadpos;
    }

    public function getAvatar(array $opts = []): string
    {
        if (config('user.avatar_provider') === 'gravatar') {
            /** Get a Gravatar URL for a specified email address.
             *
             * @param int|string $s Size in pixels, defaults to 80px [ 1 - 2048 ]
             * @param string $d Default imageset to use [ 404 | mp | identicon | monsterid | wavatar ]
             * @param string $r Maximum rating (inclusive) [ g | pg | r | x ]
             */
            $url = config('gravatar.base_url') . md5(strtolower(trim($this->email)));
            $url .= '?' . http_build_query([
                    's' => $opts['s'] ?? 80,
                    'd' => $opts['d'] ?? config('gravatar.default_fallback') ?? 'identicon',
                    'r' => $opts['r'] ?? config('gravatar.maximum_rating') ?? 'g'
                ]);
            return $url;
        }/* elseif (config('user.avatar_provider') === 'remote') {
            // For example : another Image Hosting
        }*/ else {  // config('user.avatar_provider') === 'local'
            if ($this->avatar == '') {
                $this->avatar = '/static/avatar/default_avatar.jpg';
            }
        }

        return $this->avatar;
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

    public function getLang(): string
    {
        return $this->lang;
    }

    public function getBonus(): float
    {
        return $this->bonus_seeding + $this->bonus_other;
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
                    "uid" => app()->auth->getCurUser()->getId()
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
            $require_class = config('authority.' . $require_class) ?: 1;
        }

        return $this->class >= $require_class;
    }
}
