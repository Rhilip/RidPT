<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/1/8
 * Time: 17:19
 */

namespace apps\models\form;

use Mix\Helpers\StringHelper;
use Mix\User\UserInterface;
use Mix\Validators\Validator;

use RobThree\Auth\TwoFactorAuth;
use RobThree\Auth\TwoFactorAuthException;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class UserLoginForm extends Validator
{
    public $username;
    public $password;
    public $opt;

    private $self;

    // Key Information of User Session
    private $sessionLength = 64;
    private $sessionSaveKey = 'SESSION:user_set';

    // Cookie
    private $cookieName = 'rid';
    private $cookieExpires = 0x7fffffff;
    private $cookiePath = '/';
    private $cookieDomain = '';
    private $cookieSecure = false;  // Notice : Only change this value when you first run !!!!
    private $cookieHttpOnly = true;

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        $this->sessionSaveKey = app()->user->sessionSaveKey;
        $this->cookieName = app()->user->cookieName;
    }

    public static function rules()
    {
        return [
            'username' => [
                new Assert\NotBlank(),
            ],
            'password' => [
                new Assert\NotBlank(),
                new Assert\Length([
                    'min' => 6, 'minMessage' => "Password is too Short , should at least {{ limit }} characters",
                    'max' => 40, 'maxMessage' => 'Password is too Long ( At most {{ limit }} characters )'
                ]),
                new Assert\NotEqualTo(['propertyPath' => 'username', 'message' => 'The password cannot match your username.'])
            ],
        ];
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        // FIXME It will not add the rule in self::rule() when use parent::loadValidatorMetadata()
        $rules = self::rules();
        foreach ($rules as $property => $constraints) {
            $metadata->addPropertyConstraints($property, $constraints);
        }
        $metadata->addConstraint(new Assert\Callback('validateCaptcha'));
        $metadata->addConstraint(new Assert\Callback('loadUserFromPdo'));
        $metadata->addConstraint(new Assert\Callback('isMaxLoginIpReached'));
    }

    public function loadUserFromPdo(ExecutionContextInterface $context)
    {
        $this->self = app()->pdo->createCommand("SELECT `id`,`username`,`password`,`status`,`opt`,`class` from users WHERE `username` = :uname OR `email` = :email LIMIT 1")->bindParams([
            "uname" => $this->username, "email" => $this->username,
        ])->queryOne();

        if (!$this->self) {  // User is not exist
            $context->buildViolation("This User is not exist in this site.")->addViolation();
            return;
        }

        // User's password is not correct
        if (!password_verify($this->password, $this->self["password"])) {
            $context->buildViolation("Invalid username/password")->addViolation();
            return;
        }

        // User enable 2FA but it's code is wrong
        if ($this->self["opt"]) {
            try {
                $tfa = new TwoFactorAuth(app()->config->get("base.site_name"));
                if ($tfa->verifyCode($this->self["opt"], $this->opt) == false) {
                    $context->buildViolation("2FA Validation failed")->addViolation();
                    return;
                }
            } catch (TwoFactorAuthException $e) {
                $context->buildViolation("2FA Validation failed")->addViolation();
                return;
            }
        }

        // User 's status is banned or pending~
        if (in_array($this->self["status"], [UserInterface::STATUS_BANNED, UserInterface::STATUS_PENDING])) {
            $context->buildViolation("User account is not confirmed.")->addViolation();
            return;
        }
    }

    public function isMaxLoginIpReached()
    {
        // TODO Check User Fail Login Ip Count
    }


    public function createUserSession()
    {
        $userId = $this->self['id'];

        $exist_session_count = app()->redis->zCount($this->sessionSaveKey, $userId, $userId);
        if ($exist_session_count < app()->config->get('base.max_per_user_session')) {
            do { // To make sure this session is unique!
                $userSessionId = StringHelper::getRandomString($this->sessionLength);
                $count = app()->pdo->createCommand('SELECT COUNT(`id`) FROM `users_session_log` WHERE sid = :sid')->bindParams([
                    'sid' => $userSessionId
                ])->queryScalar();
            } while ($count != 0);

            // store user login information , ( for example `login ip`,`platform`,`browser`,`last activity at` )
            $ua = app()->request->getUserAgent(true);

            app()->pdo->createCommand('INSERT INTO `users_session_log`(`uid`, `sid`, `login_ip`, `browser`, `platform` , `last_access_at`) ' .
                'VALUES (:uid,:sid,INET6_ATON(:login_ip),:browser,:platform, NOW())')->bindParams([
                'uid' => $userId, 'sid' => $userSessionId,
                'login_ip' => app()->request->getClientIp(),
                'browser' => $ua->getClient('name') . ' ' . $ua->getClient('version'),
                'platform' => $ua->getOs('name') .' '. $ua->getOs('version') .' '. $ua->getOs('platform')
            ])->execute();

            // Add this session id in Redis Cache
            app()->redis->zAdd($this->sessionSaveKey, $userId, $userSessionId);

            // Set User Cookie
            app()->response->setCookie($this->cookieName, $userSessionId, $this->cookieExpires, $this->cookiePath, $this->cookieDomain, $this->cookieSecure, $this->cookieHttpOnly);
            return true;
        } else {
            return false;
        }
    }

    public function updateUserLoginInfo()
    {
        app()->pdo->createCommand("UPDATE `users` SET `last_login_at` = NOW() , `last_login_ip` = INET6_ATON(:ip) WHERE `id` = :id")->bindParams([
            "ip" => app()->request->getClientIp(), "id" => $this->self["id"]
        ])->execute();
    }
}
