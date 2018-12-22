<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2018/12/22
 * Time: 16:51
 */

namespace apps\httpd\models;

use Mix\Validators\Validator;

use Symfony\Component\Validator\Constraints as Assert;

class TestForm extends Validator
{
    public $test;

    public static function rule() {
        return [
            'test' => [new Assert\NotBlank(),new Assert\EqualTo('Mary')]
        ];
    }
}
