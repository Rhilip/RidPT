<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/1/8
 * Time: 17:19
 */

namespace apps\models\form;

use Mix\Validators\Validator;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class UserLoginForm  extends Validator
{
    public $username;
    public $password;

    private $_password;

    public static function rules()
    {
        return [
            'username' => [
                new Assert\NotBlank(),
                new Assert\Choice(['choices' => ['open', 'invite', 'green'], 'message' => "The Register Type is not allowed"])
            ],
            'password' => [
                new Assert\NotBlank(),
                new Assert\Length([
                    'min' => 6, 'minMessage' => "Password is too Short , should at least {{ limit }} characters",
                    'max' => 40, 'maxMessage' => 'Password is too Long ( At most {{ limit }} characters )'
                ]),
                new Assert\NotEqualTo(['propertyPath' => 'username', 'message' => 'The password cannot match your username.'])
            ],
            'opt' => [new Assert\Length()],
        ];
    }

    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        // FIXME It will not add the rule in self::rule() when use parent::loadValidatorMetadata()
        $rules = self::rules();
        foreach ($rules as $property => $constraints) {
            $metadata->addPropertyConstraints($property, $constraints);
        }
    }
}
