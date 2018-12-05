<?php

namespace apps\httpd\models;

use mix\validators\Validator;

class UserForm extends Validator
{

    public $username;
    public $password;
    public $password_again;
    public $email;

    // 规则
    public function rules()
    {
        return [
            'username' => [//'string', 'maxLength' => 12, 'filter' => ['trim'],
                           'call', 'callback' => [$this, 'checkUsername']],
            'password' => ['string', 'minLength' => 6, 'maxLength' => 40],
            'password_again' => ['compare', 'compareAttribute' => 'password'],
            'email' => ['email'],
        ];
    }

    // 场景
    public function scenarios()
    {
        return [
            'create' => ['required' => ['username','password','password_again','email']],
        ];
    }

    // 消息
    public function messages()
    {
        return [
            'username.required' => '名称不能为空.',
            'username.maxLength' => '名称最多不能超过25个字符.',
            'email' => '邮箱格式错误.',
        ];
    }

    public function checkUsername($fieldValue) {
        return true;
    }
}
