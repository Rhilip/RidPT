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

trait UserTrait
{
    use AttributesImportUtils;

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

    private $invites;
    private $temp_invites;

    protected $peer_status;
    protected $infoCacheKey;

    public function loadUserContentById($id)
    {
        $this->infoCacheKey = 'User:' . $id . ':base_content';
        $self = app()->redis->hGetAll($this->infoCacheKey);
        if (empty($self)) {
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

    /**
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

    /**
     * @param bool $real
     * @return mixed
     */
    public function getUploaded($real = false)
    {
        if ($real) {
            if (is_null($this->true_uploaded)) {
                $this->true_uploaded = app()->redis->hGet($this->infoCacheKey, 'true_uploaded');
                if (false === $this->true_uploaded) {
                    $this->true_uploaded = app()->pdo->createCommand('SELECT SUM(`true_downloaded`) FROM `snatched` WHERE `user_id` = :uid')->bindParams([
                            "uid" => $this->id
                        ])->queryScalar() ?? 0;
                    app()->redis->hSet($this->infoCacheKey, 'true_uploaded', $this->true_uploaded);
                }
            }
        }
        return $this->uploaded;
    }

    /**
     * @param bool $real
     * @return mixed
     */
    public function getDownloaded($real = false)
    {
        if ($real) {
            if (is_null($this->true_downloaded)) {
                $this->true_downloaded = app()->redis->hGet($this->infoCacheKey, 'true_downloaded');
                if (false === $this->true_downloaded) {
                    $this->true_downloaded = app()->pdo->createCommand('SELECT SUM(`true_downloaded`) FROM `snatched` WHERE `user_id` = :uid')->bindParams([
                            "uid" => $this->id
                        ])->queryScalar() ?? 0;
                    app()->redis->hSet($this->infoCacheKey, 'true_downloaded', $this->true_downloaded);
                }
            }
            return $this->true_downloaded;
        }
        return $this->downloaded;
    }

    public function getRatio($real = false)
    {
        $download = max(1, $this->getDownloaded($real));  // We will never let it as zero
        $upload = max(1, $this->getUploaded($real));
        return $upload / $download;
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
        $peer_status = $this->peer_status ?? app()->redis->get('User:' . $this->id . ':peer_count');
        if (is_null($peer_status) || $peer_status === false) {
            $peer_count = app()->pdo->createCommand("SELECT `seeder`, COUNT(id) FROM `peers` WHERE `user_id` = :uid GROUP BY seeder")->bindParams([
                'uid' => $this->id
            ])->queryAll() ?: [];
            $peer_status = array_merge(['yes' => 0, 'no' => 0, 'partial' => 0], $peer_count);
            $this->peer_status = $peer_status;
            app()->redis->set('User:' . $this->id . ':peer_count', $peer_status, 60);
        }
        return $seeder ? (int)$peer_status[$seeder] : $peer_status;
    }

    public function getActiveSeed()
    {
        return $this->getPeerStatus('yes');
    }

    public function getActiveLeech()
    {
        return $this->getPeerStatus('no') + $this->getPeerStatus('partial');
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
        return $this->invites;
    }

    /**
     * @return mixed
     */
    public function getTempInvites()
    {
        if (is_null($this->temp_invites)) {
            $this->temp_invites = app()->redis->hGet($this->infoCacheKey, 'temp_invite');
            if (false === $this->temp_invites) {
                $this->temp_invites = app()->pdo->createCommand('SELECT SUM(`qty`) FROM `user_invitations` WHERE `user_id` = :uid AND `qty` > 0 AND `expire_at` < NOW()')->bindParams([
                        "uid" => $this->id
                    ])->queryScalar() ?? 0;
                app()->redis->hSet($this->infoCacheKey, 'temp_invite', $this->temp_invites);
            }
        }
        return $this->temp_invites;
    }
}
