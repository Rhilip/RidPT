<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/1/8
 * Time: 20:24
 */

namespace Mix\User;

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

    public $infoSaveKeyPrefix = 'USER:CONTENT_';

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
     * @return mixed
     */
    public function getClass()
    {
        return $this->class;
    }
}
