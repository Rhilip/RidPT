<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/1/8
 * Time: 20:24
 */

namespace Mix\User;

use Mix\Exceptions\NotFoundException;
use Mix\Utils\AttributesImportUtils;

trait UserTrait
{
    use AttributesImportUtils;

    private $id;
    private $username;
    private $email;
    private $status;
    private $class;

    private $passkey;

    private $avatar;

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
    private $downloaded;
    private $seedtime;
    private $leechtime;

    public $infoSaveKeyPrefix = 'USER:content_';

    public function loadUserContentById($id)
    {
        $self = app()->redis->hGetAll($this->infoSaveKeyPrefix . $id);
        if (empty($self)) {
            $self = app()->pdo->createCommand("SELECT * FROM `users` WHERE id = :id;")->bindParams([
                "id" => $id
            ])->queryOne();
            app()->redis->hMset($this->infoSaveKeyPrefix . $id, $self);
            app()->redis->expire($this->infoSaveKeyPrefix . $id, 3 * 60);
        }
        $this->importAttributes($self);
    }

    public function loadUserContentByName($name)
    {
        $uid = app()->redis->hGet('USER:map_name_to_id', $name);
        if (false === $uid) {
            $uid = app()->pdo->createCommand('SELECT id FROM `users` WHERE LOWER(`username`) = LOWER(:uname) LIMIT 1;')->bindParams([
                'uname' => $name
            ])->queryScalar();
            app()->redis->hSet('USER:map_name_to_id', $name, $uid);
        }
        if (!$uid) {
            throw new NotFoundException('');
        }
        // TODO
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
            $upload = app()->redis->hGet($this->infoSaveKeyPrefix . $this->id, 'true_uploaded');
            if (false === $upload) {
                $upload = app()->pdo->createCommand('SELECT SUM(`true_uploaded`) FROM `snatched` WHERE `user_id` = :uid')->bindParams([
                        "uid" => $this->id
                    ])->queryScalar() ?? 0;
                app()->redis->hSet($this->infoSaveKeyPrefix . $this->id, 'true_uploaded', $upload);
            }
            return $upload;
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
            $download = app()->redis->hGet($this->infoSaveKeyPrefix . $this->id, 'true_downloaded');
            if (false === $download) {
                $download = app()->pdo->createCommand('SELECT SUM(`true_downloaded`) FROM `snatched` WHERE `user_id` = :uid')->bindParams([
                        "uid" => $this->id
                    ])->queryScalar() ?? 0;
                app()->redis->hSet($this->infoSaveKeyPrefix . $this->id, 'true_downloaded', $download);
            }
            return $download;
        }
        return $this->downloaded;
    }

    public function getRatio($real = false)
    {
        $download = max(1, $this->getDownloaded($real));  // We will never let it as zero
        $upload = max(1, $this->getUploaded($real));
        return $upload / $download;
    }

    /**
     * @param bool $interval
     * @return \DateInterval|int
     * @throws \Exception
     */
    public function getSeedtime($interval = true)
    {
        ;
        return $interval ? new \DateInterval('PT' . $this->seedtime . 'S') : $this->seedtime;
    }

    /**
     * @param bool $interval
     * @return \DateInterval|int
     * @throws \Exception
     */
    public function getLeechtime($interval = true)
    {
        return $interval ? new \DateInterval('PT' . $this->leechtime . 'S') : $this->leechtime;
    }

    public function getTimeRatio()
    {
        return max(1, $this->seedtime) / max(1, $this->leechtime);
    }
}