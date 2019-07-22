<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/1/8
 * Time: 20:24
 */

namespace apps\components\User;

use Rid\Exceptions\NotFoundException;
use Rid\Utils\AttributesImportUtils;
use Rid\Utils\ClassValueCacheUtils;

trait UserTrait
{
    use AttributesImportUtils;
    use ClassValueCacheUtils;

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

    private $invitees;
    private $pending_invites;
    private $invites;
    private $temp_invites_details;

    protected $peer_status;
    protected $infoCacheKey;

    protected function getCacheNameSpace(): string
    {
        return 'User:' . $this->id . ':base_content';
    }

    public function loadUserContentById($id)
    {
        $this->infoCacheKey = 'User:' . $id . ':base_content';
        $self = app()->redis->hGetAll($this->infoCacheKey);
        if (empty($self) || !isset($self['id'])) {
            $self = app()->pdo->createCommand("SELECT * FROM `users` WHERE id = :id;")->bindParams([
                "id" => $id
            ])->queryOne();
            app()->redis->hMset($this->infoCacheKey, $self);
            app()->redis->expire($this->infoCacheKey, 10 * 60);
        }
        $this->importAttributes($self);

    }

    public function loadUserContentByName($name)
    {
        $uid = app()->redis->hGet('User_Map:name_to_id', $name);
        if (false === $uid) {
            $uid = app()->pdo->createCommand('SELECT id FROM `users` WHERE LOWER(`username`) = LOWER(:uname) LIMIT 1;')->bindParams([
                'uname' => $name
            ])->queryScalar();
            app()->redis->hSet('User_Map:name_to_id', $name, $uid);
        }
        if ($uid) {
            $this->loadTorrentContentById($uid);
        }
        throw new NotFoundException('This user is not found');
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

        return $raw ? $this->class : UserInterface::ROLE[$this->class];
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
        return max(1, $this->seedtime) / max(1, $this->leechtime);
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
                    "uid" => app()->user->getId()
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
}
