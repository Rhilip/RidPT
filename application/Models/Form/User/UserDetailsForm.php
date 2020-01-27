<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 8/12/2019
 * Time: 2019
 */

namespace App\Models\Form\User;

use App\Models\Form\Traits\isValidUserTrait;
use Rid\Validators\Validator;

class UserDetailsForm extends Validator
{
    use isValidUserTrait;

    protected $_autoload = true;
    protected $_autoload_from = ['get'];

    public static function defaultData(): array
    {
        return [
            'id' => app()->auth->getCurUser()->getId()
        ];
    }

    public static function inputRules(): array
    {
        return [
            'user_id' => 'Integer', 'uid' => 'Integer', 'id' => 'Required | Integer',
        ];
    }
}
