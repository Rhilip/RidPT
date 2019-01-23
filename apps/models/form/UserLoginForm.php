<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/1/8
 * Time: 17:19
 */

namespace apps\models\form;

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
        $metadata->addConstraint(new Assert\Callback('loadUserFromPDO'));
    }

    public function loadUserFromPDO(ExecutionContextInterface $context)
    {
        $this->self = app()->pdo->createCommand("SELECT `id`,`username`,`password`,`status`,`opt`,`class` from users WHERE `username` = :uname OR `email` = :email LIMIT 1")->bindParams([
            "uname" => $this->username, "email" => $this->username,
        ])->queryOne();

        if (!$this->self)  // User is not exist
            $context->buildViolation("This User is not exist in this site.")->addViolation();

        // User's password is not correct
        if (!password_verify($this->password, $this->self["password"]))
            $context->buildViolation("Invalid username/password")->addViolation();

        // User enable 2FA but it's code is wrong
        if ($this->self["opt"]) {
            try {
                $tfa = new TwoFactorAuth(app()->config->get("base.site_name"));
                if ($tfa->verifyCode($this->self["opt"], $this->opt) == false)
                    $context->buildViolation("2FA Validation failed")->addViolation();
            } catch (TwoFactorAuthException $e) {
                $context->buildViolation("2FA Validation failed")->addViolation();
            }
        }

        // User 's status is banned or pending~
        if (in_array($this->self["status"], [UserInterface::STATUS_BANNED, UserInterface::STATUS_PENDING])) {
            $context->buildViolation("User account is not confirmed.")->addViolation();
        }
    }

    public function createUserSession()
    {
        app()->user->createUserSessionId();
        app()->user->set('user', $this->self);

        // TODO record user login session

        $this->updateUserLogin();
    }

    private function updateUserLogin() {
        app()->pdo->createCommand("UPDATE `users` SET `last_login_at` = NOW() , `last_login_ip` = INET6_ATON(:ip) WHERE `id` = :id")->bindParams([
            "ip" => app()->request->getClientIp(), "id" => $this->self["id"]
        ])->execute();
    }
}
