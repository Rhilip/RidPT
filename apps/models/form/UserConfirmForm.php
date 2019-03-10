<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/2/8
 * Time: 18:51
 */

namespace apps\models\form;

use Rid\User\UserInterface;
use Rid\Validators\Validator;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class UserConfirmForm extends Validator
{

    protected $id;
    private $uid;
    public $secret;

    public static function rules()
    {
        return [
            'secret' => [new Assert\NotBlank()]
        ];
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        parent::loadValidatorMetadata($metadata);
        $metadata->addConstraint(new Assert\Callback('validConfirmSecret'));
    }

    public function validConfirmSecret(ExecutionContextInterface $context)
    {
        if (is_null($this->secret))
            return;

        $record = app()->pdo->createCommand(
            'SELECT `users_confirm`.`id`,`users_confirm`.`uid`,`users`.`status` FROM `users_confirm` 
                  LEFT JOIN `users` ON `users`.`id` = `users_confirm`.`uid`
                  WHERE `serect` = :uid LIMIT 1;')->bindParams([
            'uid' => $this->secret
        ])->queryOne();

        if ($record == false) {  // It means this confirm key is not exist
            $context->buildViolation('This confirm key is not exist')->addViolation();
            return;
        }

        if ($record['status'] !== UserInterface::STATUS_PENDING) {
            $context->buildViolation('User Already Confirmed')->addViolation();
            return;
        }
        $this->uid = $record['uid'];
        $this->id = $record['id'];
    }

    public function flush()
    {
        app()->pdo->createCommand('UPDATE `users` SET `status` = :s WHERE `id` = :uid')->bindParams([
            's' => UserInterface::STATUS_CONFIRMED, 'uid' => $this->uid
        ])->execute();
        app()->pdo->createCommand('DELETE FROM `users_confirm` WHERE id = :id')->bindParams([
            'id' => $this->id
        ])->execute();
        app()->redis->del('User:content_' . $this->uid);
    }
}
