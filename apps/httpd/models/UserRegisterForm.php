<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2018/12/22
 * Time: 18:57
 */

namespace apps\httpd\models;

use apps\common\libraries\SiteLog;
use apps\common\libraries\SitePM;
use Mix\Facades\Config;
use Mix\Facades\PDO;
use Mix\Facades\Request;
use Mix\Facades\SwiftMailer;
use Mix\Helpers\StringHelper;
use Mix\Validators\Validator;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class UserRegisterForm extends Validator
{
    public $id;

    public $username;
    public $password;
    public $password_again;
    public $email;
    public $accept_tos = 0;

    public $type = 'open';

    public $invite_by = 0;
    public $invite_hash = "";
    public $confirm_way;

    public $passkey;
    public $status;
    public $class;
    public $uploadpos;
    public $downloadpos;
    public $uploaded;
    public $downloaded;
    public $seedtime;
    public $leechtime;
    public $bonus;

    public function importAttributes($config)
    {
        parent::importAttributes($config);
        $this->buildDefaultValue();
    }

    public function buildDefaultValue()
    {
        $this->status = Config::get("register.user_default_status") ?? User::STATUS_PENDING;
        $this->class = Config::get("register.user_default_class") ?? User::ROLE_USER;
        $this->uploadpos = Config::get("register.user_default_uploadpos") ?? 1;
        $this->downloadpos = Config::get("register.user_default_downloadpos") ?? 1;
        $this->uploaded = Config::get("register.user_default_uploaded") ?? 1;
        $this->downloaded = Config::get("register.user_default_downloaded") ?? 1;
        $this->seedtime = Config::get("register.user_default_seedtime") ?? 0;
        $this->leechtime = Config::get("register.user_default_leechtime") ?? 0;
        $this->bonus = Config::get("register.user_default_bonus") ?? 0;
    }

    public static function rule()
    {
        return [
            'type' => [
                new Assert\NotBlank(),
                new Assert\Choice(['choices' => ['open', 'invite', 'green'], 'message' => "The Register Type is not allowed"])
            ],
            'username' => [
                new Assert\NotBlank(),
                new Assert\Length(['max' => 12, 'maxMessage' => "User name is too log ({{ value }}/{{ limit }})."])
            ],
            'password' => [
                new Assert\NotBlank(),
                new Assert\Length([
                    'min' => 6, 'minMessage' => "Password is too Short , should at least {{ limit }} characters",
                    'max' => 40, 'maxMessage' => 'Password is too Long ( At most {{ limit }} characters )'
                ]),
                new Assert\NotEqualTo(['propertyPath' => 'username','message'=> 'The password cannot match your username.'])
            ],
            'password_again' => [new Assert\NotBlank(), new Assert\EqualTo(['propertyPath' => 'password','message'=>'Password is not matched.'])],
            'email' => [new Assert\NotBlank(), new Assert\Email()],
            'accept_tos' => [new Assert\NotBlank(), new Assert\EqualTo(1)],
        ];
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        // FIXME It will not add the rule in self::rule() when use parent::loadValidatorMetadata()
        $rules = self::rule();
        foreach ($rules as $property => $constraints) {
            $metadata->addPropertyConstraints($property, $constraints);
        }
        
        $metadata->addConstraint(new Assert\Callback('isRegisterSystemOpen'));
        $metadata->addConstraint(new Assert\Callback('isMaxUserReached'));
        $metadata->addConstraint(new Assert\Callback('isMaxRegisterIpReached'));
        $metadata->addConstraint(new Assert\Callback('isValidUsername'));
        $metadata->addConstraint(new Assert\Callback('isValidEmail'));
        $metadata->addConstraint(new Assert\Callback('checkRegisterType'));
    }

    public function isRegisterSystemOpen(ExecutionContextInterface $context)
    {
        if (Config::get("base.enable_register_system") != true)
            $context->buildViolation("The register isn't open in this site.")->addViolation();
        if (Config::get("register.by_" . $this->type) != true)
            $context->buildViolation("The register by {$this->type} ways isn't open in this site.")->addViolation();
    }

    public function isMaxUserReached(ExecutionContextInterface $context)
    {
        if (Config::get("register.max_user_check") &&
            User::fetchUserCount() >= Config::get("base.max_user")) {
            $context->buildViolation("Max user limit Reached")->addViolation();
        }
    }

    public function isMaxRegisterIpReached(ExecutionContextInterface $context)
    {
        if (Config::get("register.max_ip_check")) {
            $client_ip = Request::getClientIp();

            $max_user_per_ip = Config::get("register.per_ip_user") ?: 5;
            $user_ip_count = PDO::createCommand("SELECT COUNT(`id`) FROM `users` WHERE `register_ip` = INET6_ATON(:ip)")->bindParams([
                "ip" => $client_ip
            ])->queryScalar();

            if ($user_ip_count > $max_user_per_ip)
                $context->buildViolation("The register member count in this ip `$client_ip` is reached")->addViolation();
        }
    }

    public function isValidUsername(ExecutionContextInterface $context)
    {
        $username = $this->username;
        // The following characters are allowed in user names
        if (strspn(strtolower($username), "abcdefghijklmnopqrstuvwxyz0123456789_") != strlen($username))
            $context->buildViolation("Invalid characters in user names.")->addViolation();

        $count = PDO::createCommand("SELECT COUNT(`id`) FROM `users` WHERE `username` = :username")->bindParams([
            "username" => $username
        ])->queryScalar();
        if ($count > 0)
            $context->buildViolation("The user name `$username` is already used.")->addViolation();
    }

    public function isValidEmail(ExecutionContextInterface $context)
    {
        $email = $this->email;
        $email_suffix = substr($email, strpos($email, '@'));  // Will get `@test.com` as example
        if (Config::get("register.enabled_email_black_list") &&
            Config::get("register.email_black_list")) {
            $email_black_list = explode(",", Config::get("register.email_black_list"));
            if (in_array($email_suffix, $email_black_list))
                $context->buildViolation("The email suffix `$email_suffix` is not allowed.")->addViolation();
        }
        if (Config::get("register.enabled_email_white_list") &&
            Config::get("register.email_white_list")) {
            $email_white_list = explode(",", Config::get("register.email_white_list"));
            if (!in_array($email_suffix, $email_white_list))
                $context->buildViolation("The email suffix `$email_suffix` is not allowed.")->addViolation();
        }

        $email_check = PDO::createCommand("SELECT COUNT(`id`) FROM `users` WHERE `email` = :email")->bindParams([
            "email" => $email
        ])->queryScalar();
        if ($email_check > 0)
            $context->buildViolation("Email Address '$email' is already used.")->addViolation();
    }

    public function checkRegisterType(ExecutionContextInterface $context)
    {
        if ($this->type == 'invite') {
            if (strlen($this->invite_hash) != 32) {
                $context->buildViolation("This invite hash : `$this->invite_hash` is not valid")->addViolation();
            } else {
                $inviteInfo = PDO::createCommand("SELECT * FROM `invite` WHERE `hash`=:invite_hash")->bindParams([
                    "invite_hash" => $this->invite_hash
                ])->queryOne();
                if (!$inviteInfo) {
                    $context->buildViolation("This invite hash : `$this->invite_hash` is not exist")->addViolation();
                } else {
                    if ($inviteInfo["expire_at"] < time())
                        $context->buildViolation("This invite hash is expired at " . $inviteInfo["expire_at"] . ".")->addViolation();
                }
            }
        } elseif ($this->type == 'green') {
            /**
             * Function that you used to valid that user can register by green ways
             * By default , It will only throw a NotImplementViolation
             *
             * For example, Judged by their email suffix , or you can use other method like OATH2
             *
             * if (strpos($user_email,"@rhilip.info") !== false)
             *    // Do something to update $ret_array
             *
             * If register pass the Green Check , you can also update some status of this Users.
             * If he don't pass this check , you should throw Exception with **enough** message.
             *
             */
            $context->buildViolation("The Green way to register in this site is not Implemented.")->addViolation();
        }
    }

    public function flush()
    {
        $this->passkey = StringHelper::md5($this->username . date("Y-m-d H:i:s"), 10);
        $this->confirm_way = Config::get("register.user_confirm_way");

        /**
         * Set The First User enough privilege ,
         * so that He needn't email (or other way) to confirm his account ,
         * and can access the (super)admin panel to change site config .
         */
        if (User::fetchUserCount() == 0) {
            $this->status = User::STATUS_CONFIRMED;
            $this->class = User::ROLE_STAFFLEADER;
            $this->confirm_way = "auto";
        }

        switch ($this->confirm_way) {
            case "auto":
                $this->status = User::STATUS_CONFIRMED;
                break;
            case "mod":
                break;
            case "email":
            default:
                {
                    // FIXME send mail or other confirm way to active this new user (change it's status to `confirmed`)
                    SwiftMailer::send([$this->email], "Please confirm your accent", "Click this link to confirm.");
                }
        }

        PDO::createCommand("INSERT INTO `users` (`username`, `password`, `email`, `status`, `class`, `passkey`, `invite_by`, `create_at`, `register_ip`, `uploadpos`, `downloadpos`, `uploaded`, `downloaded`, `seedtime`, `leechtime`, `bonus_other`) 
                                 VALUES (:name, :passhash, :email, :status, :class, :passkey, :invite_by, CURRENT_TIMESTAMP, INET6_ATON(:ip), :uploadpos, :downloadpos, :uploaded, :downloaded, :seedtime, :leechtime, :bonus)")->bindParams(array(
            "name" => $this->username, "passhash" => password_hash($this->password, PASSWORD_DEFAULT), "email" => $this->email,
            "status" => $this->status, "class" => $this->class, "passkey" => $this->passkey,
            "invite_by" => $this->invite_by, "ip" => Request::getClientIp(),
            "uploadpos" => $this->uploadpos, "downloadpos" => $this->downloadpos,
            "uploaded" => $this->uploaded, "downloaded" => $this->downloaded,
            "seedtime" => $this->seedtime, "leechtime" => $this->leechtime,
            "bonus" => $this->bonus
        ))->execute();
        $this->id = PDO::getLastInsertId();

        if ($this->type == 'invite') {
            PDO::createCommand("DELETE from `invite` WHERE `hash` = :invite_hash")->bindParams([
                "invite_hash" => $this->invite_hash,
            ])->execute();

            // FIXME Send PM to inviter
            SitePM::send(0, $this->invite_by, "New Invitee Signup Successful", "New Invitee Signup Successful");
        }

        SiteLog::write("User $this->username($this->id) is created now" . (
            $this->type == "invite" ? ", Invite by " : ""
            ), SiteLog::LEVEL_MOD);
    }
}
