<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/1/2020
 * Time: 2020
 */

declare(strict_types=1);

namespace App\Forms\Traits;

use App\Libraries\Constant;

/**
 * Class RegisterCheckTrait
 * @package App\Forms\Traits
 */
trait UserRegisterCheckTrait
{
    abstract protected function getRegisterType();

    /** @noinspection PhpUnused */
    protected function isRegisterSystemOpen()
    {
        if (config('base.enable_register_system') != true) {
            $this->buildCallbackFailMsg('RegisterSystemOpen', 'The register isn\'t open in this site.');
            return;
        }

        $register_type = $this->getRegisterType();
        if (config('register.by_' . $register_type) != true) {
            $this->buildCallbackFailMsg('RegisterSystemOpen', "The register by {$this->getInput('type')} ways isn't open in this site.");
            return;
        }
    }

    /** @noinspection PhpUnused */
    protected function isMaxUserReached()
    {
        if (config('register.check_max_user') &&
            container()->get('site')->fetchUserCount() >= config('base.max_user')) {
            $this->buildCallbackFailMsg('MaxUserReached', 'Max user limit Reached');
        }
    }

    /** @noinspection PhpUnused */
    protected function isValidUsername()
    {
        $username = $this->getInput('username');

        // The following characters are allowed in user names
        if (strspn(strtolower($username), 'abcdefghijklmnopqrstuvwxyz0123456789_') != strlen($username)) {
            $this->buildCallbackFailMsg('ValidUsername', 'Invalid characters in user names.');
            return;
        }

        // Check if this username is in blacklist or not
        if (container()->get('redis')->sIsMember(Constant::siteBannedUsernameSet, $username)) {
            $this->buildCallbackFailMsg('ValidUsername', 'This username is in our blacklist.');
            return;
        }

        // Check this username is exist in Table `users` or not
        $count = container()->get('pdo')->prepare('SELECT COUNT(`id`) FROM `users` WHERE `username` = :username')->bindParams([
            'username' => $username
        ])->queryScalar();
        if ($count > 0) {
            $this->buildCallbackFailMsg('ValidUsername', "The user name `$username` is already used.");
            return;
        }
    }

    /** @noinspection PhpUnused */
    protected function isValidEmail()
    {
        $email = $this->getInput('email');
        $email_suffix = substr($email, strpos($email, '@'));  // Will get `@test.com` as example

        if (config('register.check_email_blacklist') &&
            in_array($email_suffix, config('register.email_black_list'))) {
            $this->buildCallbackFailMsg('ValidEmail', "The email suffix `$email_suffix` is not allowed.");
            return;
        }

        if (config('register.check_email_whitelist') &&
            !in_array($email_suffix, config('register.email_white_list'))) {
            $this->buildCallbackFailMsg('ValidEmail', "The email suffix `$email_suffix` is not allowed.");
            return;
        }

        // Check $email is in blacklist or not
        if (container()->get('redis')->sIsMember(Constant::siteBannedEmailSet, $email)) {
            $this->buildCallbackFailMsg('ValidEmail', 'This email is in our blacklist.');
            return;
        }

        $email_check = container()->get('pdo')->prepare('SELECT COUNT(`id`) FROM `users` WHERE `email` = :email')->bindParams([
            "email" => $email
        ])->queryScalar();
        if ($email_check > 0) {
            $this->buildCallbackFailMsg('ValidEmail', "Email Address '$email' is already used.");
            return;
        }
    }
}
