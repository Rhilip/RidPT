<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 8/12/2019
 * Time: 2019
 */

namespace apps\models\form\Traits;


class isValidUserTrait
{
    public $id;
    public $uid;
    public $username;

    public function getUser()
    {
        $uid = $this->uid ?? $this->id;
        return app()->site->getUser($uid);
    }
}
