<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 6/1/2020
 * Time: 2020
 */

declare(strict_types=1);

namespace App\Forms\Auth;

use App\Enums\Site\LogLevel;
use App\Enums\User\Role as UserRole;
use App\Enums\User\Status as UserStatus;
use App\Forms\Traits\CaptchaTrait;
use App\Forms\Traits\UserRegisterCheckTrait;
use Rid\Utils\Random;
use Rid\Validators\AbstractValidator;
use Symfony\Component\Validator\Constraints as Assert;

class RegisterForm extends AbstractValidator
{
    use CaptchaTrait;
    use UserRegisterCheckTrait;

    private array $invite_info = [];

    private ?string $status;
    private ?string $confirm_way;

    protected function loadInputMetadata(): Assert\Collection
    {
        $rules = [
            'type' => new Assert\Choice(['open', 'invite', 'green']),
            'username' => new Assert\Length(['max' => 12]),
            'password' => [
                new Assert\Length(['min' => 6, 'max' => 40]),
                new Assert\NotIdenticalTo($this->getInput('username'))
            ],
            'password_again' => new Assert\IdenticalTo($this->getInput('password')),
            'email' => new Assert\Email(),
            'verify_tos' => new Assert\IsTrue(),
            'verify_age' => new Assert\IsTrue()
        ];

        if ($this->getInput('type') === 'invite') {
            $rules['invite_hash'] = new Assert\Length(32);
        }

        return new Assert\Collection($rules);
    }

    protected function loadCallbackMetaData(): array
    {
        return [
            'validateCaptcha',
            'isRegisterSystemOpen', 'isMaxUserReached',
            'isValidUsername', 'isValidEmail',
            'isMaxRegisterIpReached',
            'checkRegisterType'
        ];
    }

    public function flush(): void
    {
        // Build Some default UserData
        $status = config('register.user_default_status') ?? UserStatus::PENDING;
        $class = config('register.user_default_class') ?? UserRole::USER;
        $passkey = md5($this->getInput('username') . date('Y-m-d H:i:s') . Random::alnum(10));
        $uploadpos = config('register.user_default_uploadpos') ?? 1;
        $downloadpos = config('register.user_default_downloadpos') ?? 1;
        $uploaded = config('register.user_default_uploaded') ?? 1;
        $downloaded = config('register.user_default_downloaded') ?? 1;
        $seedtime = config('register.user_default_seedtime') ?? 0;
        $leechtime = config('register.user_default_leechtime') ?? 0;
        $bonus = config('register.user_default_bonus') ?? 0;
        $confirm_way = config('register.user_confirm_way') ?? 'auto';
        $invites = config('register.user_default_invites') ?? 0;

        // FIX UserData by some rules
        if ($this->getInput('type') === 'green') {
            // If register pass the Green Check , you can also update some status of this Site.
        }

        /**
         * Set The First User enough privilege ,
         * so that He needn't email (or other way) to confirm his account ,
         * and can access the (super)admin panel to change site config .
         */
        if (container()->get('site')->fetchUserCount() == 0) {
            $status = UserStatus::CONFIRMED;
            $class = UserRole::STAFFLEADER;
            $confirm_way = 'auto';
        }

        // User status should be confirmed if site confirm_way is auto
        if ($confirm_way == 'auto' and $status != UserStatus::CONFIRMED) {
            $status = UserStatus::CONFIRMED;
        }

        // Insert into Database
        $passhash = password_hash($this->getInput('password'), PASSWORD_DEFAULT);
        $invite_by = $this->invite_info['inviter_id'] ?? 0;

        container()->get('pdo')->prepare("INSERT INTO `users` (`username`, `password`, `email`, `status`, `class`, `passkey`, `invite_by`, `create_at`, `register_ip`, `uploadpos`, `downloadpos`, `uploaded`, `downloaded`, `seedtime`, `leechtime`, `bonus_other`,`invites`)
                                 VALUES (:name, :passhash, :email, :status, :class, :passkey, :invite_by, CURRENT_TIMESTAMP, INET6_ATON(:ip), :uploadpos, :downloadpos, :uploaded, :downloaded, :seedtime, :leechtime, :bonus, :invites)")->bindParams(array(
            'name' => $this->getInput('username'), 'passhash' => $passhash, 'email' => $this->getInput('email'),
            'status' => $status, 'class' => $class, 'passkey' => $passkey,
            'invite_by' => $invite_by, 'ip' => container()->get('request')->getClientIp(),
            'uploadpos' => $uploadpos, 'downloadpos' => $downloadpos,
            'uploaded' => $uploaded, 'downloaded' => $downloaded,
            'seedtime' => $seedtime, 'leechtime' => $leechtime,
            'bonus' => $bonus, 'invites' => $invites
        ))->execute();
        $user_id = container()->get('pdo')->getLastInsertId();

        // TODO Newcomer exams

        $this->sendNewerPM($user_id);

        $log_text = "User {$this->getInput('username')}($user_id) is created now.";

        // Send Invite Success PM to invitee
        if ($this->getInput('type') == 'invite') {
            $this->sendInviteePM($invite_by, $log_text);
        }

        // Send Confirm Email
        if ($confirm_way == 'email') {
            $this->sendConfirmEmail($user_id);
        }

        // Add Site log for user signup
        container()->get('site')->writeLog($log_text, LogLevel::MOD);

        // FIXME Set some value with used by Controller
        $this->status = $status;
        $this->confirm_way = $confirm_way;
    }

    /**
     * @return string|null
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * @return string|null
     */
    public function getConfirmWay(): ?string
    {
        return $this->confirm_way;
    }

    private function sendInviteePM($invitee_id, &$log_text)
    {
        container()->get('pdo')->prepare("UPDATE `invite` SET `used` = 1 WHERE `hash` = :invite_hash")->bindParams([
            "invite_hash" => $this->getInput('invite_hash'),
        ])->execute();

        $invitee = container()->get(\App\Entity\User\UserFactory::class)->getUserById($invitee_id);
        $log_text .= '(Invite by ' . $invitee->getUsername() . '(' . $invitee->getId() . ')).';

        container()->get('site')->sendPM(
            0,
            $invitee_id,
            'New Invitee Signup Successful',
            'New Invitee Signup Successful'
        );
    }

    private function sendNewerPM($newer_id)
    {
        container()->get('site')->sendPM(
            0,
            $newer_id,
            'Welcome to Our Site',
            'Welcome to Our Site'
        );
    }

    private function sendConfirmEmail($newer_id)
    {
        $confirm_key = Random::alnum(32);
        container()->get('pdo')->prepare('INSERT INTO `user_confirm` (`uid`,`secret`,`create_at`,`action`) VALUES (:uid,:secret,CURRENT_TIMESTAMP,:action)')->bindParams([
            'uid' => $newer_id, 'secret' => $confirm_key, 'action' => 'register'
        ])->execute();
        $confirm_url = container()->get('request')->getSchemeAndHttpHost() . '/auth/confirm/register?secret=' . $confirm_key;

        container()->get('site')->sendEmail(
            $this->getInput('email'),
            'Please confirm your accent',
            'email/user_register',
            [
                'username' => $this->getInput('username'),
                'confirm_url' => $confirm_url,
            ]
        );
    }

    /** @noinspection PhpUnused */
    protected function isMaxRegisterIpReached()
    {
        if (config('register.check_max_ip')) {
            $client_ip = container()->get('request')->getClientIp();

            $max_user_per_ip = config('register.per_ip_user') ?: 5;
            $user_ip_count = container()->get('pdo')->prepare('SELECT COUNT(`id`) FROM `users` WHERE `register_ip` = INET6_ATON(:ip)')->bindParams([
                "ip" => $client_ip
            ])->queryScalar();

            if ($user_ip_count > $max_user_per_ip) {
                $this->buildCallbackFailMsg('MaxRegisterIpReached', "The register member count in this ip `$client_ip` is reached");
            }
        }
    }

    /** @noinspection PhpUnused */
    protected function checkRegisterType()
    {
        $type = $this->getInput('type');
        if ($type == 'invite') {
            $inviteInfo = container()->get('pdo')->prepare('SELECT * FROM `invite` WHERE `hash`= :invite_hash AND `used` = 0 AND `expire_at` > NOW() LIMIT 1;')->bindParams([
                'invite_hash' => $this->getInput('invite_hash')
            ])->queryOne();
            if (false === $inviteInfo) {
                $this->buildCallbackFailMsg('Invite', "This invite hash is not exist or may already used or expired.");
                return;
            }

            // TODO config key of enable username check
            if ($this->getInput('username') != $inviteInfo['username']) {
                $this->buildCallbackFailMsg('Invite', "This invite username is not match.");
                return;
            }

            $this->invite_info = $inviteInfo;
        } elseif ($type == 'green') {
            /**
             * Function that you used to valid that user can register by green ways
             * By default , It will only throw a NotImplementViolation
             *
             * For example, Judged by their email suffix , or you can use other method like OATH2
             *
             * if (strpos($user_email,"@rhilip.info") !== false)
             *    // Do something to update $ret_array
             *
             * If he don't pass this check , you should `buildCallbackFailMsg` with **enough** message.
             */
            $this->buildCallbackFailMsg('Green', "The Green way to register in this site is not Implemented.");
            return;
        }
    }

    protected function getRegisterType()
    {
        return $this->getInput('type');
    }
}
