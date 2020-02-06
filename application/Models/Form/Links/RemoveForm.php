<?php
/**
 * Created by PhpStorm.
 * User: Rhilip
 * Date: 2019/6/16
 * Time: 10:23
 */

namespace App\Models\Form\Links;

use Rid\Validators\Validator;

class RemoveForm extends Validator
{
    public $link_id;

    public static function inputRules(): array
    {
        return [
            'link_id' => 'Required | Integer',
        ];
    }

    public static function callbackRules(): array
    {
        return ['checkExistLinksById'];
    }

    /** @noinspection PhpUnused */
    protected function checkExistLinksById()
    {
        $count = app()->pdo->prepare('SELECT COUNT(`id`) FROM `links` WHERE id = :id')->bindParams([
            'id' => $this->getInput('link_id')
        ])->queryScalar();
        if ($count == 0) {
            $this->buildCallbackFailMsg('Link:exist', 'This link is exist in our site , Please don\'t report it again and again');
        }
    }

    public function flush()
    {
        app()->pdo->prepare('DELETE FROM `links` WHERE id = :id')->bindParams([
            'id' => $this->link_id
        ])->execute();
        app()->redis->del('Site:links');
    }
}
