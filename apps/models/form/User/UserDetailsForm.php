<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 8/12/2019
 * Time: 2019
 */

namespace apps\models\form\User;


use apps\models\form\Traits\isValidUserTrait;
use Rid\Validators\Validator;

class UserDetailsForm extends Validator
{
    use isValidUserTrait;

    protected $_autoload = true;
    protected $_autoload_from = ['get'];

    public static function defaultData(): array
    {
        return [
            'id' => app()->auth->getCurUser()->getId()
        ];
    }

    public static function inputRules(): array
    {
        return [
            'user_id' => 'Integer', 'uid' => 'Integer', 'id' => 'Required | Integer',
        ];
    }

    public function getCreateAt()
    {
        return $this->getUserData('create_at');
    }

    public function getLastLoginAt()
    {
        return $this->getUserData('last_login_at');
    }

    public function getLastAccessAt()
    {
        return $this->getUserData('last_access_at');
    }

    public function getLastUploadAt()
    {
        return $this->getUserData('last_upload_at');
    }

    public function getLastDownloadAt()
    {
        return $this->getUserData('last_download_at');
    }

    public function getLastConnectAt()
    {
        return $this->getUserData('last_connect_at');
    }

    public function getRegisterIp()
    {
        return inet_ntop($this->getUserData('register_ip'));
    }

    public function getLastLoginIp()
    {
        return inet_ntop($this->getUserData('last_login_ip'));
    }

    public function getLastAccessIp()
    {
        return inet_ntop($this->getUserData('last_access_ip'));
    }

    public function getLastTrackerIp()
    {
        return inet_ntop($this->getUserData('last_tracker_ip'));
    }
}
