<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 8/12/2019
 * Time: 2019
 */

namespace App\Models\Form\Traits;

use App\Models\User;

trait isValidUserTrait
{
    public $id;  // User Id

    /** @var User */
    protected $user;
    protected $user_data;  // Full user line in table `users`

    public static function inputRules(): array
    {
        return [
            'id' => 'Required | Integer',
        ];
    }

    public static function callbackRules(): array
    {
        return ['isExistUser'];
    }

    /** @noinspection PhpUnused */
    protected function isExistUser()
    {
        $uid = $this->getInput('id');
        $this->user_data = app()->pdo->createCommand('SELECT * FROM users WHERE id = :uid LIMIT 1')->bindParams([
            'uid' => $uid
        ])->queryOne();
        if ($this->user_data === false) {
            $this->buildCallbackFailMsg('User', 'The user id (' . $uid . ') is not exist in our database');
        }
        $this->user = new User($uid);  // FIXME twice db load
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getUserData($key = null, $default = null)
    {
        return is_null($key) ? $this->user_data : ($this->user_data[$key] ?? $default);
    }

}
